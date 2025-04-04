<?php

use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::group(['prefix'=> 'manage'], function() {

        // Product Routes
        Route::group(['prefix' => 'product'], function () {
            Route::get('/', [ProductController::class, 'index'])->name('product.index');
            Route::get('/create', [ProductController::class, 'create'])->name('product.create');
            Route::post('/', [ProductController::class, 'store'])->name('product.store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
            Route::post('/{product}/edit', [ProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
        });


        // Category Routes
        Route::group(['prefix' => 'category'], function () {
            Route::get('/', [CategoryController::class, 'index'])->name('category.index');
            Route::get('/create', [CategoryController::class, 'create'])->name('category.create');
            Route::post('/', [CategoryController::class, 'store'])->name('category.store');
            Route::get('/{category}', [CategoryController::class, 'show'])->name('category.show');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('category.edit');
            Route::post('/{category}/edit', [CategoryController::class, 'update'])->name('category.update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('category.destroy');
        });
    });
});
