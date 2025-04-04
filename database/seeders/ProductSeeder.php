<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $electronicsId = Category::where('slug', 'electronics')->first()->id;
        $clothingId = Category::where('slug', 'clothing')->first()->id;
        $homeKitchenId = Category::where('slug', 'home-kitchen')->first()->id;

        $products = [
            [
                'name' => 'Smartphone X',
                'slug' => 'smartphone-x',
                'description' => 'Latest smartphone with advanced features',
                'price' => 699.99,
                'stock' => 50,
                'sku' => 'PHONE-X-001',
                'category_id' => $electronicsId,
            ],
            [
                'name' => 'Laptop Pro',
                'slug' => 'laptop-pro',
                'description' => 'Powerful laptop for professionals',
                'price' => 1299.99,
                'stock' => 25,
                'sku' => 'LAPTOP-P-001',
                'category_id' => $electronicsId,
            ],
            [
                'name' => 'Men\'s T-shirt',
                'slug' => 'mens-tshirt',
                'description' => 'Comfortable cotton t-shirt',
                'price' => 19.99,
                'stock' => 100,
                'sku' => 'CLOTH-T-001',
                'category_id' => $clothingId,
            ],
            [
                'name' => 'Women\'s Jeans',
                'slug' => 'womens-jeans',
                'description' => 'Stylish women\'s jeans',
                'price' => 49.99,
                'stock' => 75,
                'sku' => 'CLOTH-J-001',
                'category_id' => $clothingId,
            ],
            [
                'name' => 'Coffee Maker',
                'slug' => 'coffee-maker',
                'description' => 'Automatic coffee maker for home use',
                'price' => 89.99,
                'stock' => 30,
                'sku' => 'HOME-C-001',
                'category_id' => $homeKitchenId,
            ],
            [
                'name' => 'Blender',
                'slug' => 'blender',
                'description' => 'High-speed blender for smoothies and more',
                'price' => 59.99,
                'stock' => 40,
                'sku' => 'HOME-B-001',
                'category_id' => $homeKitchenId,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
