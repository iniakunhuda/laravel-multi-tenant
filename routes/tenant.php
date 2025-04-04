<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\TenantAssetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // Tenant homepage
    Route::get('/', function () {
        return Inertia::render('tenant/Welcome', [
            'tenantId' => tenant('id'),
            'tenantName' => tenant('name')
        ]);
    })->name('home');

    Route::get('tenant-asset/{path}', TenantAssetController::class)
        ->where('path', '.*')
        ->name('tenant.asset');

    // Include tenant-specific auth routes
    require __DIR__.'/tenant/auth.php';
    require __DIR__.'/tenant/admin.php';

    // Tenant dashboard and other protected routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('tenant/Dashboard', [
                'tenantId' => tenant('id'),
                'tenantName' => tenant('name')
            ]);
        })->name('dashboard');
    });
});
