<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Tests\TestCase;

class MultiTenantTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenants = [];
    protected $centralUsers = [];
    protected $timestamp;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $timestamp = time();
        $this->timestamp = $timestamp;

        // Create demo tenants
        $tenants = [
            [
                'id' => 'test_store1_'.$this->timestamp,
                'name' => 'Demo Store 1',
                'domain' => 'store1.localhost',
                'email' => 'admin@store1.com',
            ],
            [
                'id' => 'test_store2_'.$this->timestamp,
                'name' => 'Demo Store 2',
                'domain' => 'store2.localhost',
                'email' => 'admin@store2.com',
            ],
        ];

        foreach ($tenants as $index => $tenantData) {
            $tenant = Tenant::create($tenantData);

            if ($index === 0) {
                $this->tenants['store1'] = $tenant;
            } else {
                $this->tenants['store2'] = $tenant;
            }

            $tenant->domains()->create(['domain' => $tenantData['domain']]);
        }
    }

    /**
     * Test that tenants are properly isolated and have separate databases.
     *
     * @return void
     */
    public function test_tenants_have_isolated_databases()
    {
        // Create a product in store1
        $this->tenants['store1']->run(function () {
            $category = Category::create([
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic products',
                'is_active' => true,
            ]);

            Product::create([
                'name' => 'Test Product Store 1',
                'slug' => 'test-product-store-1',
                'description' => 'This is a test product for Store 1',
                'price' => 99.99,
                'stock' => 10,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'TP-123456',
            ]);

            // Verify product exists in store1
            $this->assertDatabaseHas('products', [
                'name' => 'Test Product Store 1',
            ]);
        });

        // Create a different product in store2
        $this->tenants['store2']->run(function () {
            $category = Category::create([
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Clothing products',
                'is_active' => true,
            ]);

            Product::create([
                'name' => 'Test Product Store 2',
                'slug' => 'test-product-store-2',
                'description' => 'This is a test product for Store 2',
                'price' => 49.99,
                'stock' => 20,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'TP-654321',
            ]);

            // Verify product exists in store2
            $this->assertDatabaseHas('products', [
                'name' => 'Test Product Store 2',
            ]);
        });

        // Verify store1 doesn't have store2's product
        $this->tenants['store1']->run(function () {
            $this->assertDatabaseMissing('products', [
                'name' => 'Test Product Store 2',
            ]);
        });

        // Verify store2 doesn't have store1's product
        $this->tenants['store2']->run(function () {
            $this->assertDatabaseMissing('products', [
                'name' => 'Test Product Store 1',
            ]);
        });
    }

    /**
     * Test user authentication and authorization within tenant context.
     *
     * @return void
     */
    public function test_user_authentication_and_authorization()
    {
        // Create users in each tenant
        $store1AdminEmail = 'admin1@example.com';
        $store1CustomerEmail = 'customer1@example.com';
        $store2AdminEmail = 'admin2@example.com';
        $store2CustomerEmail = 'customer2@example.com';

        // Create users in store1
        $this->tenants['store1']->run(function () use ($store1AdminEmail, $store1CustomerEmail) {
            // Admin user
            User::create([
                'name' => 'Admin Store 1',
                'email' => $store1AdminEmail,
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);

            // Customer user
            User::create([
                'name' => 'Customer Store 1',
                'email' => $store1CustomerEmail,
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);
        });

        // Create users in store2
        $this->tenants['store2']->run(function () use ($store2AdminEmail, $store2CustomerEmail) {
            // Admin user
            User::create([
                'name' => 'Admin Store 2',
                'email' => $store2AdminEmail,
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);

            // Customer user
            User::create([
                'name' => 'Customer Store 2',
                'email' => $store2CustomerEmail,
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);
        });

        // Verify user isolation between tenants
        $this->tenants['store1']->run(function () use ($store2AdminEmail, $store2CustomerEmail) {
            $this->assertDatabaseMissing('users', [
                'email' => $store2AdminEmail,
            ]);
            $this->assertDatabaseMissing('users', [
                'email' => $store2CustomerEmail,
            ]);
        });

        $this->tenants['store2']->run(function () use ($store1AdminEmail, $store1CustomerEmail) {
            $this->assertDatabaseMissing('users', [
                'email' => $store1AdminEmail,
            ]);
            $this->assertDatabaseMissing('users', [
                'email' => $store1CustomerEmail,
            ]);
        });
    }

    /**
     * Test shopping cart functionality within tenant context.
     *
     * @return void
     */
    public function test_cart_functionality()
    {
        // Setup test data for store1
        $this->tenants['store1']->run(function () {
            // Create category and product
            $category = Category::create([
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic products',
                'is_active' => true,
            ]);

            $product = Product::create([
                'name' => 'Laptop',
                'slug' => 'laptop',
                'description' => 'Powerful laptop',
                'price' => 999.99,
                'stock' => 5,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'LAP-123456',
            ]);

            // Create a user
            $user = User::create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);

            // Create a cart for the user
            $cart = Cart::create([
                'user_id' => $user->id,
            ]);

            // Add product to cart
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);

            // Verify cart item exists
            $this->assertDatabaseHas('cart_items', [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ]);

            // Check if cart item is correctly associated with the cart
            $cartWithItems = Cart::with('items')->find($cart->id);
            $this->assertEquals(1, $cartWithItems->items->count());
            $this->assertEquals($product->id, $cartWithItems->items->first()->product_id);
        });

        // Setup different test data for store2
        $this->tenants['store2']->run(function () {
            // Create category and product
            $category = Category::create([
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Clothing products',
                'is_active' => true,
            ]);

            $product = Product::create([
                'name' => 'T-Shirt',
                'slug' => 't-shirt',
                'description' => 'Cotton t-shirt',
                'price' => 19.99,
                'stock' => 20,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'TSH-654321',
            ]);

            // Create a user
            $user = User::create([
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);

            // Create a cart for the user
            $cart = Cart::create([
                'user_id' => $user->id,
            ]);

            // Add product to cart
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => $product->price,
            ]);

            // Verify cart item exists
            $this->assertDatabaseHas('cart_items', [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 2,
            ]);
        });

        // Verify cart isolation between tenants
        $this->tenants['store1']->run(function () {
            $carts = Cart::with('items')->get();
            foreach ($carts as $cart) {
                foreach ($cart->items as $item) {
                    // Verify each cart item refers to a product that exists in this tenant
                    $product = Product::find($item->product_id);
                    $this->assertNotNull($product, "Product {$item->product_id} not found in store1");
                }
            }
        });

        $this->tenants['store2']->run(function () {
            $carts = Cart::with('items')->get();
            foreach ($carts as $cart) {
                foreach ($cart->items as $item) {
                    // Verify each cart item refers to a product that exists in this tenant
                    $product = Product::find($item->product_id);
                    $this->assertNotNull($product, "Product {$item->product_id} not found in store2");
                }
            }
        });
    }

    /**
     * Test order processing functionality within tenant context.
     *
     * @return void
     */
    public function test_order_processing()
    {
        // Process an order in store1
        $this->tenants['store1']->run(function () {
            // Create category and product
            $category = Category::create([
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic products',
                'is_active' => true,
            ]);

            $product = Product::create([
                'name' => 'Laptop',
                'slug' => 'laptop',
                'description' => 'Powerful laptop',
                'price' => 999.99,
                'stock' => 5,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'LAP-123456',
            ]);

            // Create a user
            $user = User::create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);

            // Create an order
            $order = Order::create([
                'order_number' => 'ORD-' . uniqid(),
                'user_id' => $user->id,
                'total' => 999.99,
                'status' => 'pending',
                'billing_name' => 'John Doe',
                'billing_email' => 'john@example.com',
                'billing_phone' => '1234567890',
                'billing_address' => '123 Main St',
                'billing_city' => 'New York',
                'billing_state' => 'NY',
                'billing_country' => 'USA',
                'billing_zipcode' => '10001',
                'shipping_name' => 'John Doe',
                'shipping_address' => '123 Main St',
                'shipping_city' => 'New York',
                'shipping_state' => 'NY',
                'shipping_country' => 'USA',
                'shipping_zipcode' => '10001',
                'payment_method' => 'Bank Transfer',
                'payment_status' => 'pending',
            ]);

            // Create order item
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'subtotal' => $product->price,
            ]);

            // Update product stock
            $product->stock -= 1;
            $product->save();

            // Verify order exists
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'user_id' => $user->id,
            ]);

            // Verify order item exists
            $this->assertDatabaseHas('order_items', [
                'order_id' => $order->id,
                'product_id' => $product->id,
            ]);

            // Verify product stock was updated
            $this->assertEquals(4, Product::find($product->id)->stock);
        });

        // Process a different order in store2
        $this->tenants['store2']->run(function () {
            // Create category and product
            $category = Category::create([
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Clothing products',
                'is_active' => true,
            ]);

            $product = Product::create([
                'name' => 'T-Shirt',
                'slug' => 't-shirt',
                'description' => 'Cotton t-shirt',
                'price' => 19.99,
                'stock' => 20,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'TSH-654321',
            ]);

            // Create a user
            $user = User::create([
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);

            // Create an order
            $order = Order::create([
                'order_number' => 'ORD-' . uniqid(),
                'user_id' => $user->id,
                'total' => 39.98,
                'status' => 'processing',
                'billing_name' => 'Jane Smith',
                'billing_email' => 'jane@example.com',
                'billing_phone' => '0987654321',
                'billing_address' => '456 Elm St',
                'billing_city' => 'Los Angeles',
                'billing_state' => 'CA',
                'billing_country' => 'USA',
                'billing_zipcode' => '90001',
                'shipping_name' => 'Jane Smith',
                'shipping_address' => '456 Elm St',
                'shipping_city' => 'Los Angeles',
                'shipping_state' => 'CA',
                'shipping_country' => 'USA',
                'shipping_zipcode' => '90001',
                'payment_method' => 'paypal',
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            // Create order item
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 2,
                'price' => $product->price,
                'subtotal' => $product->price * 2,
            ]);

            // Update product stock
            $product->stock -= 2;
            $product->save();

            // Verify order exists
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'user_id' => $user->id,
            ]);

            // Verify order item exists
            $this->assertDatabaseHas('order_items', [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

            // Verify product stock was updated
            $this->assertEquals(18, Product::find($product->id)->stock);
        });

        // Verify order isolation between tenants
        $this->tenants['store1']->run(function () {
            $orders = Order::with('items')->get();
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    // Verify each order item refers to a product that exists in this tenant
                    $product = Product::find($item->product_id);
                    $this->assertNotNull($product, "Product {$item->product_id} not found in store1");

                    // Verify the product name matches
                    $this->assertEquals($product->name, $item->product_name);
                }
            }
        });

        $this->tenants['store2']->run(function () {
            $orders = Order::with('items')->get();
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    // Verify each order item refers to a product that exists in this tenant
                    $product = Product::find($item->product_id);
                    $this->assertNotNull($product, "Product {$item->product_id} not found in store2");

                    // Verify the product name matches
                    $this->assertEquals($product->name, $item->product_name);
                }
            }
        });
    }

    /**
     * Test tenant database creation and initialization.
     *
     * @return void
     */
    public function test_tenant_database_creation()
    {
        // Create a new tenant
        $tenant = Tenant::create([
            'id' => 'test_store3_'.$this->timestamp,
            'name' => 'Test Store 3',
            'email' => 'store3@example.com',
            'is_active' => true,
            'data' => [],
        ]);

        $tenant->domains()->create(['domain' => 'store3.localhost']);

        // Database should be created by the Tenancy lifecycle hooks
        $this->assertTrue($tenant instanceof TenantWithDatabase);

        // Initialize the tenant database with migrations
        $tenant->run(function () {
            $this->artisan('migrate');

            // Test that we can create data in this new tenant
            $user = User::create([
                'name' => 'Store 3 Admin',
                'email' => 'admin3@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);

            $this->assertDatabaseHas('users', [
                'email' => 'admin3@example.com',
            ]);
        });

        // Verify the data is isolated to the new tenant
        $this->tenants['store1']->run(function () {
            $this->assertDatabaseMissing('users', [
                'email' => 'admin3@example.com',
            ]);
        });
    }

    /**
     * Test guest shopping cart persistence with session IDs.
     *
     * @return void
     */
    public function test_guest_cart_persistence()
    {
        $this->tenants['store1']->run(function () {
            // Create a test product
            $category = Category::create([
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic products',
                'is_active' => true,
            ]);

            $product = Product::create([
                'name' => 'Headphones',
                'slug' => 'headphones',
                'description' => 'Wireless headphones',
                'price' => 99.99,
                'stock' => 10,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'HPH-123456',
            ]);

            // Create a session ID
            $sessionId = uniqid('session_', true);

            // Create a cart with the session ID
            $cart = Cart::create([
                'session_id' => $sessionId,
            ]);

            // Add an item to the cart
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);

            // Verify the item exists in the cart
            $this->assertDatabaseHas('cart_items', [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ]);

            // Simulate retrieving the cart by session ID
            $retrievedCart = Cart::where('session_id', $sessionId)->first();
            $this->assertNotNull($retrievedCart);
            $this->assertEquals($cart->id, $retrievedCart->id);

            // Verify the session cart is correctly associated with its items
            $retrievedCart->load('items');
            $this->assertEquals(1, $retrievedCart->items->count());
            $this->assertEquals($product->id, $retrievedCart->items->first()->product_id);
        });
    }

    /**
     * Test transferring a guest cart to a user after login.
     *
     * @return void
     */
    public function test_guest_cart_transfer_after_login()
    {
        $this->tenants['store1']->run(function () {
            // Create a test product
            $category = Category::create([
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic products',
                'is_active' => true,
            ]);

            $product = Product::create([
                'name' => 'Headphones',
                'slug' => 'headphones',
                'description' => 'Wireless headphones',
                'price' => 99.99,
                'stock' => 10,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'HPH-123456',
            ]);

            // Create a session ID for guest
            $sessionId = uniqid('session_', true);

            // Create a guest cart with the session ID
            $guestCart = Cart::create([
                'session_id' => $sessionId,
            ]);

            // Add an item to the guest cart
            CartItem::create([
                'cart_id' => $guestCart->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);

            // Create a user
            $user = User::create([
                'name' => 'Guest User',
                'email' => 'guest@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);

            // Simulate transferring the guest cart to the user after login
            $guestCart->user_id = $user->id;
            $guestCart->session_id = null; // Clear the session ID
            $guestCart->save();

            // Verify the cart is now associated with the user
            $this->assertDatabaseHas('carts', [
                'id' => $guestCart->id,
                'user_id' => $user->id,
                'session_id' => null,
            ]);

            // Verify we can retrieve the cart by user ID
            $userCart = Cart::where('user_id', $user->id)->first();
            $this->assertNotNull($userCart);
            $this->assertEquals($guestCart->id, $userCart->id);

            // Verify the cart items are still intact
            $userCart->load('items');
            $this->assertEquals(1, $userCart->items->count());
            $this->assertEquals($product->id, $userCart->items->first()->product_id);
        });
    }

    /**
     * Test database connection switching during tenant requests.
     *
     * @return void
     */
    public function test_database_connection_switching()
    {
        // Check initial connection is central
        $initialConnection = DB::getDefaultConnection();
        $this->assertEquals('sqlite', $initialConnection);

        // Switch to store1's connection
        $this->tenants['store1']->run(function () {
            // Inside tenant context, connection should be tenant connection
            $tenantConnection = DB::getDefaultConnection();
            $this->assertEquals('tenant', $tenantConnection);

            // Should be able to query tenant-specific tables
            $this->assertTrue(Schema::hasTable('users'));
            $this->assertTrue(Schema::hasTable('products'));
        });

        // Connection should switch back to central after tenant context ends
        $finalConnection = DB::getDefaultConnection();
        $this->assertEquals('sqlite', $finalConnection);
    }

    /**
     * Test concurrent tenant operations maintaining proper isolation.
     *
     * @return void
     */
    public function test_concurrent_tenant_operations()
    {
        // Prepare test data in both tenants
        $store1ProductId = null;
        $store2ProductId = null;

        $this->tenants['store1']->run(function () use (&$store1ProductId) {
            $category = Category::create([
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronics products',
                'is_active' => true,
            ]);

            $product = Product::create([
                'name' => 'Store 1 Product',
                'slug' => 'store-1-product',
                'description' => 'Product in Store 1',
                'price' => 99.99,
                'stock' => 10,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'S1P-123456',
            ]);

            $store1ProductId = $product->id;
        });

        $this->tenants['store2']->run(function () use (&$store2ProductId) {
            $category = Category::create([
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Clothing products',
                'is_active' => true,
            ]);

            $product = Product::create([
                'name' => 'Store 2 Product',
                'slug' => 'store-2-product',
                'description' => 'Product in Store 2',
                'price' => 49.99,
                'stock' => 20,
                'is_active' => true,
                'category_id' => $category->id,
                'sku' => 'S2P-654321',
            ]);

            $store2ProductId = $product->id;
        });

        // Simulate concurrent operations in both tenants
        // These operations should be isolated and not interfere with each other
        $store1Results = null;
        $store2Results = null;

        // Update product in store1
        $this->tenants['store1']->run(function () use ($store1ProductId, &$store1Results) {
            $product = Product::find($store1ProductId);
            $product->price = 109.99;
            $product->save();

            // Get the updated product
            $store1Results = Product::find($store1ProductId);
        });

        // Update product in store2
        $this->tenants['store2']->run(function () use ($store2ProductId, &$store2Results) {
            $product = Product::find($store2ProductId);
            $product->price = 39.99;
            $product->save();

            // Get the updated product
            $store2Results = Product::find($store2ProductId);
        });

        // Verify each tenant's operations were isolated
        $this->assertEquals(109.99, $store1Results->price);
        $this->assertEquals(39.99, $store2Results->price);

        // Verify the updates didn't cross-contaminate
        $this->tenants['store1']->run(function () use ($store1ProductId) {
            $product = Product::find($store1ProductId);
            $this->assertEquals(109.99, $product->price);
        });

        $this->tenants['store2']->run(function () use ($store2ProductId) {
            $product = Product::find($store2ProductId);
            $this->assertEquals(39.99, $product->price);
        });
    }

    protected function tearDown(): void
    {
        // Delete tenant databases
        foreach ($this->tenants as $tenant) {
            try {
                $tenant->delete();
                // Or force delete if necessary:
                // $tenant->database()->delete();
            } catch (\Exception $e) {
                // Ignore errors on teardown
            }
        }

        parent::tearDown();
    }
}
