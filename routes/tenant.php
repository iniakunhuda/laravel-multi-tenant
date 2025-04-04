<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\HomepageController;
use App\Http\Controllers\Tenant\OrderController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\Manage\TenantAssetController;
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
    Route::get('/home', [HomepageController::class, 'index'])->name('home');

    // Product routes
    Route::resource('products', ProductController::class)->only(['show']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class, 'updateItem'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'removeItem'])->name('cart.remove');
    Route::delete('/cart/clear/{cart}', [CartController::class, 'clearCart'])->name('cart.clear');

    // Checkout and order routes
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/confirmation/{order}', [OrderController::class, 'confirmation'])->name('orders.confirmation');


    Route::get('tenant-asset/{path}', TenantAssetController::class)
        ->where('path', '.*')
        ->name('tenant.asset');

    // Include tenant-specific auth routes
    require __DIR__.'/tenant/auth.php';

    // Tenant dashboard and other protected routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('tenant/Dashboard', [
                'tenantId' => tenant('id'),
                'tenantName' => tenant('name')
            ]);
        })->name('dashboard');
        require __DIR__.'/tenant/admin.php';
    });
});
