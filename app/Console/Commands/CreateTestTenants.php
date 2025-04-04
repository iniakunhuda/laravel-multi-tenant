<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;

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
    protected $description = 'Create test tenants with users for development';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Creating test tenants...');

        // Create first tenant
        $tenant1 = Tenant::create([
            'id' => 'tenant1',
            'name' => 'Demo Store tenant1',
            'email' => 'info@tenant1.com'
        ]);
        $tenant1->domains()->create(['domain' => 'tenant1.localhost']);
        $this->info('Created tenant: tenant1 with domain tenant1.localhost');

        // Create second tenant
        $tenant2 = Tenant::create([
            'id' => 'tenant2',
            'name' => 'Demo Store tenant2',
            'email' => 'info@tenant2.com'
        ]);
        $tenant2->domains()->create(['domain' => 'tenant2.localhost']);
        $this->info('Created tenant: tenant2 with domain tenant2.localhost');

        // Create users inside each tenant's database
        $this->info('Creating users for each tenant...');

        Tenant::all()->runForEach(function ($tenant) {
            $this->info("Creating user for tenant: {$tenant->id}");

            // Initialize tenancy to switch to tenant database
            tenancy()->initialize($tenant);

            // Create a user using factory
            User::factory()->create([
                'email' => "admin@{$tenant->id}.com",
                'name' => "Admin {$tenant->id}",
                'role' => 'admin'
            ]);

            // Create a regular customer user
            User::factory()->create([
                'email' => "customer@{$tenant->id}.com",
                'name' => "Customer {$tenant->id}",
                'role' => 'customer'
            ]);

            // End tenancy context
            tenancy()->end();
        });

        $this->info('Test tenants created successfully!');

        return Command::SUCCESS;
    }
}
