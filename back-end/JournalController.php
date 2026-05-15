<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Exception;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    /**
     * READ: Get all journals with their translated fields
     * Route: GET /api/journals
     */
    public function index()
    {
        $journals = Journal::with('translations')->get();

        $formattedJournals = $journals->map(function ($journal) {
            return [
                'JournalID' => $journal->JournalID,
                'Name' => $journal->localized_title, 
                'Description' => $journal->localized_description,
                'SourceLink' => $journal->SourceLink,
                'Image' => $journal->Image,
            ];
        });

        return response()->json($formattedJournals);
    }

    /**
     * READ: Get a single journal by ID
     * Route: GET /api/journals/{journal}
     */
    public function show($journalId)
    {
        $journal = Journal::with('translations')
            ->where('JournalID', $journalId)
            ->firstOrFail();

        return response()->json([
            'JournalID' => $journal->JournalID,
            'Name' => $journal->localized_title,
            'Description' => $journal->localized_description,
            'SourceLink' => $journal->SourceLink,
            'Image' => $journal->Image,
            'LastUpdated' => $journal->LastUpdated,
        ]);
    }

    /**
     * CREATE: Add a new journal (Scraper Only)
     * Route: POST /api/journal
     */
    public function store(Request $request)
    {
        #error_log("inside the store func");
        $validatedData = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string',
            'SourceLink' => 'required|string|max:100',
            'Image' => 'nullable|string', 
            
            'translations' => 'nullable|array',
            'translations.*.LocaleID' => 'required|exists:locales,LocaleID',
            'translations.*.Title' => 'required|string|max:150',
            'translations.*.Description' => 'nullable|string'
        ]);
        #error_log("validated");
        $imageBinary = null;
        if (!empty($validatedData['Image'])) {
            $imageParts = explode(',', $validatedData['Image']);
            $imageBase64 = count($imageParts) > 1 ? $imageParts[1] : $imageParts[0];
            $imageBinary = base64_decode($imageBase64);
        }
        #error_log("image handled");

        try{
            $journal = Journal::create([
                'Name' => $validatedData['Name'],
                'Description' => $validatedData['Description'],
                'SourceLink' => $validatedData['SourceLink'],
                'Image' => $imageBinary,
            ]);

            #error_log("journal created");
            if (!empty($validatedData['translations'])) {
                foreach ($validatedData['translations'] as $translation) {
                    $journal->translations()->create([
                        'LocaleID' => $translation['LocaleID'],
                        'Title' => $translation['Title'],
                        'Description' => $translation['Description'],
                    ]);
                }
            }
            #error_log("translations saved");
        }
        catch(Exception $e)
        {
            error_log($e);
        }

        return response()->json([
            'message' => 'Journal created successfully!', 
        ], 201);
    }
}