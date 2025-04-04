<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'remember_token' => Str::random(10),
        ]);

        // Create demo customer
        User::create([
            'name' => 'Demo Customer',
            'email' => 'customer@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'customer',
            'remember_token' => Str::random(10),
        ]);
    }
}
