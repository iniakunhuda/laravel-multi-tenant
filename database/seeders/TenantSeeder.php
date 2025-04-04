<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Stancl\Tenancy\Database\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo tenants
        $tenants = [
            [
                'id' => 'store1',
                'name' => 'Demo Store 1',
                'domain' => 'store1.localhost',
                'email' => 'admin@store1.com',
            ],
            [
                'id' => 'store2',
                'name' => 'Demo Store 2',
                'domain' => 'store2.localhost',
                'email' => 'admin@store2.com',
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::create($tenantData);
            $tenant->domains()->create(['domain' => $tenantData['domain']]);
        }
    }
}
