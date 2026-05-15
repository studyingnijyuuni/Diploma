<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('mangas')) {{Schema::create('mangas', function (Blueprint $table) {
            $table->id('MangaID');
            $table->string('Title', 150);
            $table->unsignedBigInteger('JournalID');
            $table->string('SourceLink', 100);
            $table->date('LastReleaseDate')->nullable();
            $table->date('UpcomingReleaseDate')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->binary('Image')->nullable();
            $table->string('ReleasesInfo', 255)->nullable();
            $table->string('LastChapterName', 50)->nullable();
            $table->dateTime('LastUpdated')->useCurrent()->useCurrentOnUpdate();

            // Foreign Key
            $table->foreign('JournalID')->references('JournalID')->on('journals')->onDelete('cascade');

            DB::statement('ALTER TABLE mangas MODIFY Image MEDIUMBLOB');
        });}}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mangas');
    }
};
