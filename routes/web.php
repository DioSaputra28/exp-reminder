<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductRequestController as AdminProductRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\BarcodeScanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController as UserProductController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackedItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // User login page (shows Google login button)
    Route::view('/login', 'auth.login')->name('login');

    // Google OAuth
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

    // Admin login
    Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit')->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');
    Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{status}', [DashboardController::class, 'filtered'])->name('dashboard.filtered');

    // Barcode Scanner
    Route::get('/scan', [BarcodeScanController::class, 'index'])->name('scan.index');
    Route::post('/scan/lookup', [BarcodeScanController::class, 'lookup'])->name('scan.lookup');

    // Tracked Items
    Route::get('/tracked-items', [TrackedItemController::class, 'index'])->name('tracked-items.index');
    Route::get('/tracked-items/create', [TrackedItemController::class, 'create'])->name('tracked-items.create');
    Route::get('/tracked-items/create/{product}', [TrackedItemController::class, 'createForProduct'])->name('tracked-items.create-for-product');
    Route::post('/tracked-items', [TrackedItemController::class, 'store'])->name('tracked-items.store');
    Route::get('/tracked-items/{trackedItem}/edit', [TrackedItemController::class, 'edit'])->name('tracked-items.edit');
    Route::put('/tracked-items/{trackedItem}', [TrackedItemController::class, 'update'])->name('tracked-items.update');
    Route::delete('/tracked-items/{trackedItem}', [TrackedItemController::class, 'destroy'])->name('tracked-items.destroy');

    // Product catalog (read-only for users)
    Route::get('/products', [UserProductController::class, 'index'])->name('products.index');

    // Product Requests (User)
    Route::get('/product-requests', [ProductRequestController::class, 'index'])->name('product-requests.index');
    Route::get('/product-requests/create', [ProductRequestController::class, 'create'])->name('product-requests.create');
    Route::post('/product-requests', [ProductRequestController::class, 'store'])->name('product-requests.store');

    // Profile & Telegram
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Redirect root to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    /*
    |----------------------------------------------------------------------
    | Admin Routes (requires admin role)
    |----------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/toggle', [AdminUserController::class, 'toggleActive'])->name('users.toggle');
        Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Product Requests (Admin)
        Route::get('/product-requests', [AdminProductRequestController::class, 'index'])->name('product-requests.index');
        Route::put('/product-requests/{productRequest}/approve', [AdminProductRequestController::class, 'approve'])->name('product-requests.approve');
        Route::put('/product-requests/{productRequest}/reject', [AdminProductRequestController::class, 'reject'])->name('product-requests.reject');
    });
});
