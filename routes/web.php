<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\WoodPriceController;
use App\Http\Controllers\WoodTransactionController;
use App\Http\Controllers\AccountController;

// Display the login form.
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Semua rute yang membutuhkan autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Hanya admin yang boleh akses harga kayu & akun
    Route::middleware('role:admin')->group(function () {
        Route::get('/wood-prices', [WoodPriceController::class, 'index'])->name('wood_prices.index');
        Route::get('wood-prices/{hashid}/edit', [WoodPriceController::class, 'edit'])->name('wood_prices.edit');
        Route::put('wood-prices/{hashid}', [WoodPriceController::class, 'update'])->name('wood_prices.update');
        Route::resource('account', AccountController::class);
    });

    // Semua user login bisa akses transaksi & stok
    Route::get('/transaksi/beli', [WoodTransactionController::class, 'buy'])->name('wood_transactions.buy');
    Route::post('/transaksi/beli', [WoodTransactionController::class, 'storeBuy'])->name('wood_transactions.storeBuy');
    Route::get('/transaksi/jual', [WoodTransactionController::class, 'sell'])->name('wood_transactions.sell');
    Route::post('/transaksi/jual', [WoodTransactionController::class, 'storeSell'])->name('wood_transactions.storeSell');
    Route::get('/transaksi', [WoodTransactionController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{transaksi}', [WoodTransactionController::class, 'show'])->name('transaksi.show');
    Route::get('/stock/{woodType}', [App\Http\Controllers\StockController::class, 'detail'])->name('stock.detail');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});