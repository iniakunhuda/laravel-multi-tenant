<?php

use App\Http\Controllers\HomeController;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomepageController;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

Route::middleware(['web'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('central.home');
});
