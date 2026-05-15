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
        Schema::create('journal_locales', function (Blueprint $table) {
            $table->unsignedBigInteger('JournalID');
            $table->unsignedBigInteger('LocaleID');
            $table->string('Title', 50);
            $table->text('Description')->nullable();

            $table->primary(['JournalID', 'LocaleID']);
            $table->foreign('JournalID')->references('JournalID')->on('journals')->onDelete('cascade');
            $table->foreign('LocaleID')->references('LocaleID')->on('locales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_locales');
    }
};
