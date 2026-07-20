<?php

use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\BusinessLinkController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\LinkController;
use App\Http\Controllers\Business\ProfileController;
use App\Http\Controllers\Business\QRCodeController;
use App\Http\Controllers\PublicBusinessController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Business Owner (User) Routes - Root domain
Route::get('/', function () {
    if (auth()->check()) {
        // Check if user is an admin (from users table)
        if (auth()->user() instanceof User) {
            return redirect()->route('admin.dashboard');
        }

        // Otherwise it's a business user
        return redirect()->route('business.dashboard');
    }

    return inertia('Business/Login');
})->name('home');

// Default login route (required by Laravel auth)
Route::get('login', function () {
    return inertia('Business/Login');
})->middleware('guest')->name('login');

Route::middleware(['auth', 'business'])->prefix('business')->name('business.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('profile/{nanoid}', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile/{nanoid}/logo', [ProfileController::class, 'deleteLogo'])->name('profile.deleteLogo');
    Route::get('links', [LinkController::class, 'index'])->name('links');
    Route::post('links/{nanoid}', [LinkController::class, 'store'])->name('links.store');
    Route::put('links/{nanoid}/{link}', [LinkController::class, 'update'])->name('links.update');
    Route::delete('links/{nanoid}/{link}', [LinkController::class, 'destroy'])->name('links.destroy');
    Route::post('links/{nanoid}/reorder', [LinkController::class, 'reorder'])->name('links.reorder');
    Route::get('menu', [App\Http\Controllers\Business\MenuController::class, 'index'])->name('menu');
    Route::post('menu/{nanoid}/categories', [App\Http\Controllers\Business\MenuController::class, 'storeCategory'])->name('menu.categories.store');
    Route::put('menu/{nanoid}/categories/{category}', [App\Http\Controllers\Business\MenuController::class, 'updateCategory'])->name('menu.categories.update');
    Route::delete('menu/{nanoid}/categories/{category}', [App\Http\Controllers\Business\MenuController::class, 'destroyCategory'])->name('menu.categories.destroy');
    Route::post('menu/{nanoid}/categories/reorder', [App\Http\Controllers\Business\MenuController::class, 'reorderCategories'])->name('menu.categories.reorder');
    Route::post('menu/{nanoid}/categories/{category}/items', [App\Http\Controllers\Business\MenuController::class, 'storeItem'])->name('menu.items.store');
    Route::post('menu/{nanoid}/categories/{category}/items/{item}', [App\Http\Controllers\Business\MenuController::class, 'updateItem'])->name('menu.items.update');
    Route::delete('menu/{nanoid}/categories/{category}/items/{item}', [App\Http\Controllers\Business\MenuController::class, 'destroyItem'])->name('menu.items.destroy');
    Route::post('menu/{nanoid}/categories/{category}/items/reorder', [App\Http\Controllers\Business\MenuController::class, 'reorderItems'])->name('menu.items.reorder');
    Route::patch('menu/{nanoid}/categories/{category}/items/{item}/toggle-active', [App\Http\Controllers\Business\MenuController::class, 'toggleItemActive'])->name('menu.items.toggle-active');
    Route::get('qr-code', [QRCodeController::class, 'index'])->name('qr-code');
    Route::get('settings', [App\Http\Controllers\Business\SettingsController::class, 'index'])->name('settings');
    Route::put('settings/password', [App\Http\Controllers\Business\SettingsController::class, 'updatePassword'])->name('settings.password');
});

// Admin Routes - /adminos prefix
Route::prefix('adminos')->name('admin.')->group(function () {
    // Admin root - redirect to dashboard or login
    Route::get('/', function () {
        if (auth()->check() && auth()->user() instanceof User) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    });

    // Admin login page
    Route::get('login', function () {
        return inertia('Admin/Login');
    })->middleware('guest')->name('login');

    // Admin logout
    Route::post('logout', [AdminLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::inertia('dashboard', 'Admin/Dashboard')->name('dashboard');

        // Business Management
        Route::resource('businesses', BusinessController::class);
        Route::patch('businesses/{business}/toggle-active', [BusinessController::class, 'toggleActive'])
            ->name('businesses.toggle-active');

        // Business Links
        Route::resource('businesses.links', BusinessLinkController::class)
            ->except(['create', 'edit', 'show']);
        Route::post('businesses/{business}/links/reorder', [BusinessLinkController::class, 'reorder'])
            ->name('businesses.links.reorder');

        // Business Menu
        Route::get('businesses/{business}/menu', [MenuController::class, 'index'])
            ->name('businesses.menu.index');
        Route::post('businesses/{business}/categories', [MenuController::class, 'storeCategory'])
            ->name('businesses.categories.store');
        Route::put('businesses/{business}/categories/{category}', [MenuController::class, 'updateCategory'])
            ->name('businesses.categories.update');
        Route::delete('businesses/{business}/categories/{category}', [MenuController::class, 'destroyCategory'])
            ->name('businesses.categories.destroy');
        Route::post('businesses/{business}/categories/reorder', [MenuController::class, 'reorderCategories'])
            ->name('businesses.categories.reorder');
        Route::post('businesses/{business}/categories/{category}/items', [MenuController::class, 'storeItem'])
            ->name('businesses.items.store');
        Route::put('businesses/{business}/categories/{category}/items/{item}', [MenuController::class, 'updateItem'])
            ->name('businesses.items.update');
        Route::delete('businesses/{business}/categories/{category}/items/{item}', [MenuController::class, 'destroyItem'])
            ->name('businesses.items.destroy');
        Route::post('businesses/{business}/categories/{category}/items/reorder', [MenuController::class, 'reorderItems'])
            ->name('businesses.items.reorder');
        Route::patch('businesses/{business}/categories/{category}/items/{item}/toggle-active', [MenuController::class, 'toggleItemActive'])
            ->name('businesses.items.toggle-active');

        // Users Management (Business Users)
        Route::resource('users', UserController::class);

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports');

        // Settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::put('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    });
});

// Public Business Pages (with /p/ prefix to avoid conflicts)
Route::get('p/{nanoid}', [PublicBusinessController::class, 'show'])
    ->where('nanoid', '[a-zA-Z0-9_-]{8}')
    ->name('public.business.show');

Route::get('p/{nanoid}/menu', [PublicBusinessController::class, 'menu'])
    ->where('nanoid', '[a-zA-Z0-9_-]{8}')
    ->name('public.business.menu');
