<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Mengisi data awal pengguna FlipPlay.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Alice',
            'email' => 'alice@flipplay.test',
            'password' => 'password',
        ]);

        User::create([
            'name' => 'Bob',
            'email' => 'bob@flipplay.test',
            'password' => 'password',
        ]);
    }
}
