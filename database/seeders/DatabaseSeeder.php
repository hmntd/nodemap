<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'user@nodemap.dev'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
            ]
        );
    }
}
