<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateTestTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:create-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and seed test tenants';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Creating test tenants...');

        // Tenant 1 - Electronics, Clothing, Home & Kitchen Store
        $tenant1 = Tenant::create([
            'id' => 'tenant1',
            'name' => 'Electronics Store',
            'email' => 'admin@tenant1.com',
            'data' => [
                'type' => 'electronics',
                'description' => 'Store selling electronics, clothing, and home goods'
            ]
        ]);

        $tenant1->domains()->create(['domain' => 'tenant1.localhost']);

        // Tenant 2 - Food Store
        $tenant2 = Tenant::create([
            'id' => 'tenant2',
            'name' => 'Food Store',
            'email' => 'admin@tenant2.com',
            'data' => [
                'type' => 'food',
                'description' => 'Store selling groceries and food products'
            ]
        ]);

        $tenant2->domains()->create(['domain' => 'tenant2.localhost']);

        $this->info('Tenants created successfully!');

        // Seed tenant 1 with electronics data
        $this->info('Seeding Tenant 1 (Electronics Store)...');
        tenancy()->initialize($tenant1);
        Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\TenantDatabaseSeeder'
        ]);
        $this->info(Artisan::output());
        tenancy()->end();

        // Seed tenant 2 with food data
        $this->info('Seeding Tenant 2 (Food Store)...');
        tenancy()->initialize($tenant2);
        Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\FoodDatabaseSeeder'
        ]);
        $this->info(Artisan::output());
        tenancy()->end();

        $this->info('All tenants seeded successfully!');

        return Command::SUCCESS;
    }
}
