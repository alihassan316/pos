<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseEntryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/keep-alive', function () {
    session()->put('ping', time());
    return response()->json(['status' => 'ok']);
});

// Dashboard (already protected)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
	


// Everything below should require login
Route::middleware(['auth'])->group(function () {
	
	Route::get('/summary', [DashboardController::class, 'summary'])->name('summary');
	Route::post('/summary', [DashboardController::class, 'summaryFilter'])->name('summary.filter');

    // Products (protected)
    Route::resource('products', ProductController::class);
	
	Route::get('products/{product}/history', [ProductController::class, 'history'])
    ->name('products.history');
	
	Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);

    // Sales (protected)
    Route::resource('sales', SaleController::class);
    Route::post('sales/{sale}/pay-due', [SaleController::class, 'payDue'])->name('sales.pay-due');
    Route::post('sales/{sale}/return', [SaleController::class, 'processReturn'])->name('sales.return');
	Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');

    // Ajax product search for Sale page
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('products.search');
	
	Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
	
	Route::post('/expiry/update-action', [DashboardController::class, 'updateExpiryAction'])
    ->name('expiry.update-action');
	
	Route::prefix('purchases')->group(function () {
		Route::get('/', [PurchaseEntryController::class, 'index'])->name('purchases.index');
		Route::get('/create', [PurchaseEntryController::class, 'create'])->name('purchases.create');
		//Route::post('/store', [PurchaseEntryController::class, 'store'])->name('purchases.store');
		Route::post('/store', [PurchaseEntryController::class, 'save_inv'])->name('purchases.store');
		Route::get('/invoice-update/{id}', [PurchaseEntryController::class, 'invoiceUpdate'])
     ->name('invoice.update.page');
	 
	 Route::get('/invoice-edit/{id}', [PurchaseEntryController::class, 'invoiceEdit'])
     ->name('invoice.edit.page');
	 Route::post('/purchases/update/{id}', [PurchaseEntryController::class, 'update_inv'])->name('purchases.update');
	 
	 	Route::post('/invoice/{id}/add-row', [PurchaseEntryController::class, 'addTempRow'])
    ->name('invoice.add.row');
		
		Route::delete('/invoice/row/{id}', [PurchaseEntryController::class, 'deleteRowtemp'])->name('invoice.delete.row');
		
		Route::post('/submitivnoice', [PurchaseEntryController::class, 'submitivnoice'])->name('purchases.submitivnoice');
	 
		Route::get('/{id}', [PurchaseEntryController::class, 'show'])->name('purchases.show');
		Route::delete('/{id}', [PurchaseEntryController::class, 'destroy'])->name('purchases.destroy');
		
	});
	
	

    // Profile (already protected)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';