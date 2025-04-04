<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class FoodProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groceriesId = Category::where('slug', 'groceries')->first()->id;
        $bakeryId = Category::where('slug', 'bakery')->first()->id;
        $dairyId = Category::where('slug', 'dairy')->first()->id;
        $fruitsVegetablesId = Category::where('slug', 'fruits-vegetables')->first()->id;
        $meatSeafoodId = Category::where('slug', 'meat-seafood')->first()->id;

        $products = [
            // Groceries
            [
                'name' => 'Organic Rice',
                'slug' => 'organic-rice',
                'description' => 'Premium organic white rice, 2kg package',
                'price' => 6.99,
                'stock' => 100,
                'is_active' => true,
                'sku' => 'GROC-RICE-001',
                'category_id' => $groceriesId,
            ],
            [
                'name' => 'Olive Oil',
                'slug' => 'olive-oil',
                'description' => 'Extra virgin olive oil, 500ml bottle',
                'price' => 9.99,
                'stock' => 50,
                'is_active' => true,
                'sku' => 'GROC-OIL-001',
                'category_id' => $groceriesId,
            ],

            // Bakery
            [
                'name' => 'Artisan Bread',
                'slug' => 'artisan-bread',
                'description' => 'Freshly baked artisan sourdough bread',
                'price' => 4.99,
                'stock' => 30,
                'is_active' => true,
                'sku' => 'BAKE-BREAD-001',
                'category_id' => $bakeryId,
            ],
            [
                'name' => 'Chocolate Croissants',
                'slug' => 'chocolate-croissants',
                'description' => 'Pack of 4 butter chocolate croissants',
                'price' => 6.99,
                'stock' => 25,
                'is_active' => true,
                'sku' => 'BAKE-CROIS-001',
                'category_id' => $bakeryId,
            ],

            // Dairy
            [
                'name' => 'Organic Milk',
                'slug' => 'organic-milk',
                'description' => 'Organic whole milk, 1 liter',
                'price' => 3.49,
                'stock' => 80,
                'is_active' => true,
                'sku' => 'DAIRY-MILK-001',
                'category_id' => $dairyId,
            ],
            [
                'name' => 'Cheddar Cheese',
                'slug' => 'cheddar-cheese',
                'description' => 'Aged cheddar cheese, 250g',
                'price' => 5.99,
                'stock' => 45,
                'is_active' => true,
                'sku' => 'DAIRY-CHEESE-001',
                'category_id' => $dairyId,
            ],

            // Fruits & Vegetables
            [
                'name' => 'Organic Bananas',
                'slug' => 'organic-bananas',
                'description' => 'Bunch of organic bananas',
                'price' => 2.99,
                'stock' => 100,
                'is_active' => true,
                'sku' => 'FRUIT-BAN-001',
                'category_id' => $fruitsVegetablesId,
            ],
            [
                'name' => 'Fresh Spinach',
                'slug' => 'fresh-spinach',
                'description' => 'Fresh organic spinach, 200g bag',
                'price' => 2.49,
                'stock' => 60,
                'is_active' => true,
                'sku' => 'VEG-SPIN-001',
                'category_id' => $fruitsVegetablesId,
            ],

            // Meat & Seafood
            [
                'name' => 'Premium Beef Steak',
                'slug' => 'premium-beef-steak',
                'description' => 'Grass-fed beef ribeye steak, 300g',
                'price' => 12.99,
                'stock' => 20,
                'is_active' => true,
                'sku' => 'MEAT-BEEF-001',
                'category_id' => $meatSeafoodId,
            ],
            [
                'name' => 'Fresh Salmon Fillet',
                'slug' => 'fresh-salmon-fillet',
                'description' => 'Wild-caught salmon fillet, 200g',
                'price' => 9.99,
                'stock' => 15,
                'is_active' => true,
                'sku' => 'SEAFOOD-SALM-001',
                'category_id' => $meatSeafoodId,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
