<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Role management routes
    Route::resource('roles', RoleController::class);

    // User management routes
    Route::resource('users', UserController::class);

    // Unit management routes
    Route::resource('units', UnitController::class);

    // Category management routes
    Route::resource('categories', CategoryController::class);

    // Brand management routes
    Route::resource('brands', BrandController::class);

    // Product management routes
    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/opening-stock', [ProductController::class, 'updateOpeningStock'])
        ->name('products.updateOpeningStock');
    Route::get('products/{product}/latest-purchase-price', [ProductController::class, 'getLatestPurchasePrice'])
        ->name('products.latestPurchasePrice');

    // Stock Adjustment routes
    Route::post('stock-adjustment/{product}', [StockAdjustmentController::class, 'adjust'])
        ->name('stock-adjustment.adjust');
    Route::get('stock-adjustment/{product}/latest-price', [StockAdjustmentController::class, 'getLatestPurchasePrice'])
        ->name('stock-adjustment.latestPrice');

    // Stock Management routes
    Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::get('stocks/{product}', [StockController::class, 'show'])->name('stocks.show');

    // Tax management routes
    Route::resource('taxes', TaxController::class);
});

require __DIR__ . '/auth.php';
