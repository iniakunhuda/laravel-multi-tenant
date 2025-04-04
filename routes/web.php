<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// These routes will only be accessible on the central domain (localhost:8000)
// Route::middleware(['web'])->group(function () {
//     Route::get('/', function () {
//         // List all available tenants for demonstration purposes
//         $tenants = Tenant::all();
//         return Inertia::render('CentralWelcome', [
//             'tenants' => $tenants
//         ]);
//     })->name('central.home');

//     // Central dashboard (if needed)
//     Route::get('/dashboard', function () {
//         return Inertia::render('CentralDashboard');
//     })->middleware(['auth', 'verified'])->name('central.dashboard');

// });

// require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
