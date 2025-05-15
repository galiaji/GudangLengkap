<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'username' => 'testuser',
            'name'     => 'Test User',
            'email'    => 'testuser@example.com',
            'password' => Hash::make('password'), // password: "password"
        ]);

        // Optionally, create more users or use a factory for bulk seeding.
        // For example:
        // User::factory()->count(10)->create();
    }
}