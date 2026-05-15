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
        if (!Schema::hasTable('journals')) {{Schema::create('journals', function (Blueprint $table) {
            $table->id('JournalID'); // Custom PK
            $table->string('Name', 50)->unique();
            $table->string('SourceLink', 100);
            $table->text('Description')->nullable();
            $table->binary('Image'); // mediumblob
            $table->timestamp('LastUpdated')->useCurrent()->useCurrentOnUpdate();

            DB::statement('ALTER TABLE journals MODIFY Image MEDIUMBLOB');
        });}}

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
