<?php

use App\Http\Controllers\Tenant\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::group(['prefix'=> 'manage'], function() {
        Route::group(['prefix' => 'product'], function () {
            Route::get('/', [ProductController::class, 'index'])->name('product.index');
            Route::get('/create', [ProductController::class, 'create'])->name('product.create');
            Route::post('/', [ProductController::class, 'store'])->name('product.store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
            Route::post('/{product}/edit', [ProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
        });
    });
});
