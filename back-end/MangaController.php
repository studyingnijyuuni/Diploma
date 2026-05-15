<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manga;
use Illuminate\Http\Request;
use Notification;
use Exception;
use App\Notifications\MangaChapterReleased;
use App\Notifications\MangaUpcomingRelease;
use App\Models\Tag;

class MangaController extends Controller
{
    /**
     * CUSTOM READ: Get mangas by Journal ID (Paginated, Localized, and Auth-Aware)
     * Route: GET /api/journals/{journalId}/mangas?page=1&whitelist[]=2&blacklist[]=3
     */

    // If a user wants mangas that have Tags 1 and 4, but do not have Tag 7, the API call from your frontend (using fetch or axios) should look like this:
    // GET /api/journals/1/mangas?whitelist[]=1&whitelist[]=4&blacklist[]=7
    public function getByJournal(Request $request, $journalId)
    {
        $userId = $request->user('sanctum') ? $request->user('sanctum')->UserID : 0;

        $whitelist = $request->query('whitelist', []);
        $blacklist = $request->query('blacklist', []);

        $query = Manga::with('translations')
            ->where('JournalID', $journalId)
            ->orderBy('LastReleaseDate', 'DESC');

        if (!empty($whitelist)) {
            foreach ($whitelist as $tagId) {
                $query->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('tags.TagID', $tagId); 
                });
            }
        }

        if (!empty($blacklist)) {
            $query->whereDoesntHave('tags', function ($q) use ($blacklist) {
                $q->whereIn('tags.TagID', $blacklist);
            });
        }

        if ($userId) {
            $query->withExists(['favoritedBy as isFollowed' => function ($q) use ($userId) {
                $q->where('favorites.UserID', $userId);
            }]);
        }

        $mangas = $query->paginate(5);

        $mangas->getCollection()->transform(function ($manga) use ($userId) {
            return [
                'MangaID' => $manga->MangaID,
                'Title' => $manga->localized_title,
                'Image' => $manga->Image,
                'LastReleaseDate' => $manga->LastReleaseDate,
                'SourceLink' => $manga->SourceLink,
                'isFollowed' => $userId ? (int) $manga->isFollowed : 0,
            ];
        });

        return response()->json([
            'mangas' => $mangas->items(),
            'totalMangas' => $mangas->total(),
            'itemsPerPage' => $mangas->perPage(),
            'currentPage' => $mangas->currentPage()
        ]);
    }

    /**
     * CREATE: Add a new manga (Scraper Only)
     * Route: POST /api/mangas
     */
    public function store(Request $request)
    {
  
        $validatedData = $request->validate([
            'Title' => 'required|string|max:150',
            'JournalID' => 'required|exists:journals,JournalID',
            'SourceLink' => 'required|string|max:100',
            'Image' => 'nullable|string', 
            
            'translations' => 'nullable|array',
            'translations.*.LocaleID' => 'required|exists:locales,LocaleID',
            'translations.*.Title' => 'required|string|max:150',
        ]);

        $imageBinary = null;
        if (!empty($validatedData['Image'])) {
            $imageParts = explode(',', $validatedData['Image']);
            $imageBase64 = count($imageParts) > 1 ? $imageParts[1] : $imageParts[0];
            $imageBinary = base64_decode($imageBase64);
        }
        try{
            $manga = Manga::create([
                'Title' => $validatedData['Title'],
                'JournalID' => $validatedData['JournalID'],
                'SourceLink' => $validatedData['SourceLink'],
                'Image' => $imageBinary,
            ]);

            if (!empty($validatedData['translations'])) {
                foreach ($validatedData['translations'] as $translation) {
                    $manga->translations()->create([
                        'LocaleID' => $translation['LocaleID'],
                        'Title' => $translation['Title'],
                    ]);
                }
            }
        }
        catch(Exception $e)
        {
            error_log($e);
        }

        return response()->json(['message' => 'Manga created successfully!'], 201);
    }

    /**
     * READ: Get a single manga by ID
     * Route: GET /api/mangas/{manga}
     */
    public function show($mangaId)
    {
        #error_log("we got into controller");
        $manga = Manga::with('translations')->findOrFail($mangaId);

        return response()->json([
            'MangaID' => $manga->MangaID,
            'Title' => $manga->localized_title,
            'SourceLink' => $manga->SourceLink,
            'Image' => $manga->Image,
            'ReleasesInfo' => $manga->ReleasesInfo,
            'LastChapterName' => $manga->LastChapterName,
            'LastReleaseDate' => $manga->LastReleaseDate,
            'UpcomingReleaseDate' => $manga->UpcomingReleaseDate,
        ]);
    }

    /**
     * UPDATE: Update manga data
     * Route: PUT/PATCH /api/mangas/{manga}
     */
    public function update(Request $request, Manga $manga)
    {
        $request->validate([
            'LastReleaseDate' => 'required|date',
            'UpcomingReleaseDate' => 'present|nullable|date',
            
            'LastChapterName' => 'nullable|string|max:50',
            'ReleasesInfo' => 'nullable|string|max:255',
            
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:30',
        ]);

        $manga->fill($request->except(['tags']));

        $isNewRelease = $manga->isDirty('LastReleaseDate'); #for notifications
        $isUpcomingUpdate = $manga->isDirty('UpcomingReleaseDate') && !is_null($manga->UpcomingReleaseDate);

        $manga->save();

        if ($request->has('tags')) {
            $tagIds = [];
            
            foreach ($request->tags as $tagName) {
                $normalizedName = ucfirst(strtolower(trim($tagName))); 
                $tag = Tag::firstOrCreate(['Name' => $normalizedName]);
                $tagIds[] = $tag->TagID; 
            }

            $manga->tags()->sync($tagIds);
        }

        if ($isNewRelease || $isUpcomingUpdate) {
            $usersToNotify = $manga->favoritedBy;
            
            if ($isNewRelease) {
                Notification::send($usersToNotify, new MangaChapterReleased($manga));
            }
            
            if ($isUpcomingUpdate) {
                Notification::send($usersToNotify, new MangaUpcomingRelease($manga));
            }
        }

        return response()->json(['message' => 'Manga updated, tags synced, & notifications queued!']);
    }
    /**
     * DELETE: Delete manga
     * Route: DELETE /api/mangas/{manga}
     */
    public function destroy(Manga $manga)
    {
        $manga->delete();
        return response()->json(['message' => 'Manga deleted successfully.']);
    }

    /*
     * SEARCH: GET /api/search/mangas?q={ワンピース}
     * Важливо мати MeiliSearch on (У докері)
     */
    public function search(Request $request)
    {
        $searchTerm = $request->query('q', '');
        $journalId = $request->query('journalId');

        $query = Manga::search($searchTerm);

        if ($journalId) {
            $query->where('JournalID', (int) $journalId);
        }

        $mangas = $query->query(function ($builder) {
            $builder->with('translations'); 
        })->paginate(10);

        $mangas->getCollection()->transform(function ($manga) {
            return [
                'MangaID' => $manga->MangaID,
                'Title' => $manga->localized_title,
                'Image' => $manga->Image,
                'SourceLink' => $manga->SourceLink,
            ];
        });

        return response()->json([
            'mangas' => $mangas->items(),
            'total' => $mangas->total(),
            'currentPage' => $mangas->currentPage()
        ]);
    }

}