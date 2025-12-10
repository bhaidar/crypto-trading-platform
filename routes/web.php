<?php

use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TradingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/trading', [TradingController::class, 'index'])->name('trading');

    Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrdersController::class, 'store'])->name('orders.store');
    Route::delete('/orders/{order}', [OrdersController::class, 'destroy'])->name('orders.destroy');
    Route::get('/orders/open', [OrdersController::class, 'openOrders'])->name('orders.open');
    Route::get('/orderbook/{asset}', [OrdersController::class, 'orderbook'])->name('orderbook');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API Routes
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/profile', [ProfileApiController::class, 'show'])->name('api.profile');
});

require __DIR__.'/auth.php';
