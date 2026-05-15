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
        if (!Schema::hasTable('favorites')) {Schema::create('favorites', function (Blueprint $table) {
            $table->unsignedBigInteger('UserID');
            $table->unsignedBigInteger('MangaID');
            $table->boolean('IsEmailNotificationsOn')->default(false);
            $table->boolean('IsTelegramNotificationsOn')->default(false);
            $table->timestamp('AddedAt')->useCurrent();

            $table->primary(['UserID', 'MangaID']);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->foreign('MangaID')->references('MangaID')->on('mangas')->onDelete('cascade');
        });}

        if (!Schema::hasTable('mangatags')) {Schema::create('mangatags', function (Blueprint $table) {
            $table->unsignedBigInteger('TagID');
            $table->unsignedBigInteger('MangaID');

            $table->primary(['TagID', 'MangaID']);
            $table->foreign('TagID')->references('TagID')->on('tags')->onDelete('cascade'); // Assuming tags uses TagID
            $table->foreign('MangaID')->references('MangaID')->on('mangas')->onDelete('cascade');
        });}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pivot_tables');
    }
};
