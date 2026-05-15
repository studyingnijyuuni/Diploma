<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Insert a base Journal
        DB::table('journals')->insert([
            'JournalID' => 1,
            'Name' => 'Shueisha', // Base name
            'SourceLink' => 'https://example.com/shueisha',
            'Description' => 'Japanese publishing company.',
            'Image' => '', // Required field based on your SQL schema
        ]);

        // 2. Insert a base Manga linked to the Journal
        DB::table('mangas')->insert([
            'MangaID' => 1,
            'Title' => 'ワンピース', // Original Japanese Title
            'JournalID' => 1,
            'SourceLink' => 'https://example.com/onepiece',
            'LastReleaseDate' => '2023-12-01',
            'ReleasesInfo' => 'Every Sunday',
            'LastChapterName' => 'Chapter 1100',
        ]);

        // 3. Insert Journal Translation (English - LocaleID 2)
        DB::table('journal_locales')->insert([
            'JournalID' => 1,
            'LocaleID' => 2, 
            'Title' => 'Weekly Shonen Jump',
            'Description' => 'The most popular weekly manga magazine in the world.'
        ]);

        // 4. Insert Manga Translation (Ukrainian - LocaleID 3)
        DB::table('manga_locales')->insert([
            'MangaID' => 1,
            'LocaleID' => 3, 
            'Title' => 'Ван Піс'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Because you set up ON DELETE CASCADE on your foreign keys in the previous steps,
        // deleting the Journal will automatically delete the Manga and all associated Locales!
        DB::table('journals')->where('JournalID', 1)->delete();
    }
};
