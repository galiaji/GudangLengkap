<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\WoodPriceController;
use App\Http\Controllers\WoodTransactionController;

// Display the login form.
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Semua rute yang membutuhkan autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Wood Price
    Route::get('/wood-prices', [WoodPriceController::class, 'index'])->name('wood_prices.index');
    Route::get('wood-prices/{hashid}/edit', [WoodPriceController::class, 'edit'])->name('wood_prices.edit');
    Route::put('wood-prices/{hashid}', [WoodPriceController::class, 'update'])->name('wood_prices.update');

    // Form & proses beli stok
    Route::get('/transaksi/beli', [WoodTransactionController::class, 'buy'])->name('wood_transactions.buy');
    Route::post('/transaksi/beli', [WoodTransactionController::class, 'storeBuy'])->name('wood_transactions.storeBuy');

    // Form & proses jual stok
    Route::get('/transaksi/jual', [WoodTransactionController::class, 'sell'])->name('wood_transactions.sell');
    Route::post('/transaksi/jual', [WoodTransactionController::class, 'storeSell'])->name('wood_transactions.storeSell');

    // List & detail transaksi
    Route::get('/transaksi', [WoodTransactionController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{transaksi}', [WoodTransactionController::class, 'show'])->name('transaksi.show');

    // Stock detail
    Route::get('/stock/{woodType}', [App\Http\Controllers\StockController::class, 'detail'])->name('stock.detail');

    // Account management
    Route::get('/akun', [App\Http\Controllers\AccountController::class, 'index'])->name('account.manage');
    Route::get('/akun/create', [App\Http\Controllers\AccountController::class, 'create'])->name('account.create');
    Route::post('/akun', [App\Http\Controllers\AccountController::class, 'store'])->name('account.store');
    Route::get('/akun/{user}/edit', [App\Http\Controllers\AccountController::class, 'edit'])->name('account.edit');
    Route::put('/akun/{user}', [App\Http\Controllers\AccountController::class, 'update'])->name('account.update');
    Route::delete('/akun/{user}', [App\Http\Controllers\AccountController::class, 'destroy'])->name('account.destroy');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/'); // Redirect ke halaman login setelah logout
    })->middleware('auth')->name('logout');
});