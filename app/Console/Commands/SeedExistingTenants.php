<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedExistingTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed-existing {--tenant= : The ID of a specific tenant to seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run seeders for existing tenants';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            // Seed a specific tenant
            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                $this->error("Tenant with ID '{$tenantId}' not found!");
                return Command::FAILURE;
            }

            $this->seedTenant($tenant);
        } else {
            // Seed all tenants
            $tenants = Tenant::all();

            if ($tenants->isEmpty()) {
                $this->warn('No tenants found to seed!');
                return Command::SUCCESS;
            }

            $this->info('Seeding all tenants...');

            $tenants->each(function ($tenant) {
                $this->seedTenant($tenant);
            });
        }

        $this->info('Tenant seeding completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Seed a specific tenant.
     *
     * @param \App\Models\Tenant $tenant
     * @return void
     */
    private function seedTenant($tenant)
    {
        $this->info("Seeding tenant: {$tenant->id}");

        tenancy()->initialize($tenant);

        Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\TenantDatabaseSeeder'
        ]);

        $this->info(Artisan::output());

        tenancy()->end();
    }
}
