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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->enum('type', ['movie', 'anime']);
            $table->string('genre');
            $table->decimal('rating', 3, 1);
            $table->unsignedSmallInteger('release_year');

            // Atribut spesifik Movie (nullable agar seragam dalam satu tabel media)
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('director')->nullable();

            // Atribut spesifik Anime
            $table->unsignedInteger('total_episodes')->nullable();
            $table->string('studio')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
