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
        Schema::create('locales', function (Blueprint $table) {
            $table->id('LocaleID');
            $table->string('Code', 5)->unique(); // jp, en, uk
            $table->string('Title', 50);         //日本語, English, Українська
        });

        DB::table('locales')->insert([
            ['Code' => 'jp', 'Title' => '日本語'],      //  1
            ['Code' => 'en', 'Title' => 'English'],     // 2
            ['Code' => 'uk', 'Title' => 'Українська'],  // 3
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locales');
    }
};
