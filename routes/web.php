<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public product search for purchase
Route::get('/products/live-search', [App\Http\Controllers\ProductController::class, 'liveSearch'])->name('products.liveSearch');
Route::get('/purchases/search-products', [App\Http\Controllers\PurchaseController::class, 'searchProducts'])->name('purchases.searchProducts');

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

    // Purchase Management routes
    Route::resource('purchases', PurchaseController::class);
    Route::get('purchases/{product}/get-product', [PurchaseController::class, 'getProduct'])->name('purchases.getProduct');
    Route::get('purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');
    Route::post('purchases/{purchase}/payment', [PurchaseController::class, 'addPayment'])->name('purchases.payment');
    Route::get('purchases/{purchase}/return', [PurchaseController::class, 'createReturn'])->name('purchases.return');
    Route::post('purchases/{purchase}/return', [PurchaseController::class, 'storeReturn'])->name('purchases.storeReturn');

    // Supplier Management routes
    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');

    // Stock Management routes
    Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::get('stocks/{product}', [StockController::class, 'show'])->name('stocks.show');

    // Stock Adjustment routes - custom route must come BEFORE resource
    Route::get('stock_adjustments/{product}/get-stock', [StockAdjustmentController::class, 'getProductStock'])->name('stock_adjustments.getStock');
    Route::resource('stock_adjustments', StockAdjustmentController::class);

    // Tax management routes
    Route::resource('taxes', TaxController::class);

    // Payment Method management routes
    Route::resource('payment_methods', PaymentMethodController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
});

require __DIR__ . '/auth.php';
