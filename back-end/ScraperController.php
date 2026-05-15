<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Manga;
use Illuminate\Http\Request;

class ScraperController extends Controller
{
    private $mangaFields = [
        'MangaID', 
        'Title',
        'LastReleaseDate', 
        'SourceLink', 
        'UpcomingReleaseDate', 
        'ReleasesInfo'
    ];

    // GET /api/scraper/mangas
    public function getAllMangas()
    {
        $mangas = Manga::select($this->mangaFields)->get();
        return response()->json($mangas);
    }

    // GET /api/scraper/journals
    public function getAllJournals()
    {
        $journals = Journal::select('JournalID', 'Name', 'SourceLink')->get();
        return response()->json($journals);
    }

    // GET /api/scraper/journals/{journalID}/mangas
    public function getMangasByJournal($journalId)
    {
        $mangas = Manga::select($this->mangaFields)
            ->where('JournalID', $journalId)
            ->get();
            
        return response()->json($mangas);
    }

    // GET /api/scraper/journals/{journalID}/mangas/{amount}
    public function getNMangasByJournal($journalId, $amount)
    {
        $mangas = Manga::select($this->mangaFields)
            ->where('JournalID', $journalId)
            ->limit((int) $amount)
            ->get();
            
        return response()->json($mangas);
    }

    // GET /api/scraper/favorites
    public function getFavoritedMangas()
    {
        $mangas = Manga::select($this->mangaFields)
            ->has('favoritedBy')
            ->get();
            
        return response()->json($mangas);
    }
}
