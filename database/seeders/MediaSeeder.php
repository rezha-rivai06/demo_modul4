<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Mengisi katalog awal Media (Movie & Anime) dari skenario FlipPlay Modul 3
     * serta contoh relasi koleksi awal untuk pengguna Alice.
     */
    public function run(): void
    {
        $anime1 = Media::create([
            'title' => 'Sousou no Frieren',
            'type' => 'anime',
            'genre' => 'Adventure, Drama',
            'rating' => 9.1,
            'release_year' => 2023,
            'total_episodes' => 28,
            'studio' => 'Madhouse',
        ]);

        Media::create([
            'title' => 'Shingeki no Kyojin',
            'type' => 'anime',
            'genre' => 'Action, Fantasy',
            'rating' => 8.9,
            'release_year' => 2013,
            'total_episodes' => 87,
            'studio' => 'MAPPA',
        ]);

        Media::create([
            'title' => 'Spirited Away',
            'type' => 'movie',
            'genre' => 'Animation, Drama',
            'rating' => 8.6,
            'release_year' => 2001,
            'duration_minutes' => 125,
            'director' => 'Hayao Miyazaki',
        ]);

        $movie2 = Media::create([
            'title' => 'Interstellar',
            'type' => 'movie',
            'genre' => 'Sci-Fi, Drama',
            'rating' => 8.7,
            'release_year' => 2014,
            'duration_minutes' => 169,
            'director' => 'Christopher Nolan',
        ]);

        // Hubungkan koleksi awal Alice sesuai skenario demo_modul3
        $alice = User::where('email', 'alice@flipplay.test')->first();
        if ($alice) {
            $alice->media()->attach([$anime1->id, $movie2->id]);
        }
    }
}
