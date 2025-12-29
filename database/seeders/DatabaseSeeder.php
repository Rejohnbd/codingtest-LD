<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a Test User
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'), // You can set a default password
        ]);

        // Create an Admin User
        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin@admin.com'), // Password is same as email
        ]);

        User::factory()->count(20)->create();
    }
}
