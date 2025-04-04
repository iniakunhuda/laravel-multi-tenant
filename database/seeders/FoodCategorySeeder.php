<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class FoodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Groceries',
                'slug' => 'groceries',
                'description' => 'Everyday grocery items',
                'is_active' => true,
            ],
            [
                'name' => 'Bakery',
                'slug' => 'bakery',
                'description' => 'Fresh bread and pastries',
                'is_active' => true,
            ],
            [
                'name' => 'Dairy',
                'slug' => 'dairy',
                'description' => 'Milk, cheese, and dairy products',
                'is_active' => true,
            ],
            [
                'name' => 'Fruits & Vegetables',
                'slug' => 'fruits-vegetables',
                'description' => 'Fresh fruits and vegetables',
                'is_active' => true,
            ],
            [
                'name' => 'Meat & Seafood',
                'slug' => 'meat-seafood',
                'description' => 'Fresh meat and seafood products',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
