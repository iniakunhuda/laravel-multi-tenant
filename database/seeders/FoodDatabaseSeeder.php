<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FoodDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        $this->call([
            UserSeeder::class, // Reuse the existing user seeder
            FoodCategorySeeder::class,
            FoodProductSeeder::class,
        ]);
    }
}
