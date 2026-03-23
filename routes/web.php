<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard (already protected)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Everything below should require login
Route::middleware(['auth'])->group(function () {

    // Products (protected)
    Route::resource('products', ProductController::class);
	
	Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);

    // Sales (protected)
    Route::resource('sales', SaleController::class);
    Route::post('sales/{sale}/pay-due', [SaleController::class, 'payDue'])->name('sales.pay-due');
    Route::post('sales/{sale}/return', [SaleController::class, 'processReturn'])->name('sales.return');
	Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');

    // Ajax product search for Sale page
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('products.search');
	
	Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');

    // Profile (already protected)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';