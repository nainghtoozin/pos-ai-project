<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
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

    // Tax management routes
    Route::resource('taxes', TaxController::class);
});

require __DIR__ . '/auth.php';
