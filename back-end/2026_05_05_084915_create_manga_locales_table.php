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
        Schema::create('manga_locales', function (Blueprint $table) {
            $table->unsignedBigInteger('MangaID');
            $table->unsignedBigInteger('LocaleID');
            $table->string('Title', 150);

            $table->primary(['MangaID', 'LocaleID']);
            $table->foreign('MangaID')->references('MangaID')->on('mangas')->onDelete('cascade');
            $table->foreign('LocaleID')->references('LocaleID')->on('locales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manga_locales');
    }
};
