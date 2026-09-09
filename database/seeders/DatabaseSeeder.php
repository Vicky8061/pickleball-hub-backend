<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@pickleball.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // Court Owner
        User::updateOrCreate(
            ['email' => 'owner@pickleball.com'],
            [
                'name' => 'Court Owner',
                'password' => Hash::make('password123'),
                'role' => 'owner',
                'status' => 'active',
            ]
        );

        // Regular Player / User
        User::updateOrCreate(
            ['email' => 'user@pickleball.com'],
            [
                'name' => 'Player One',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
            ]
        );
    }
}
