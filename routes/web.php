<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\WoodPriceController;
use App\Http\Controllers\WoodTransactionController;

// Display the login form.
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');

// Process the login form submission.
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Semua rute yang membutuhkan autentikasi
Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Wood Price
    Route::get('/wood-prices', [WoodPriceController::class, 'index'])->name('wood_prices.index');
    Route::get('/wood-prices/{id}/edit', [WoodPriceController::class, 'edit'])->name('wood_prices.edit');
    Route::put('/wood-prices/{id}', [WoodPriceController::class, 'update'])->name('wood_prices.update');

    // Wood Transaction
    Route::get('/wood-transactions/buy', [WoodTransactionController::class, 'buy'])->name('wood_transactions.buy');
    Route::post('/wood-transactions/buy', [WoodTransactionController::class, 'storeBuy'])->name('wood_transactions.storeBuy');
    Route::get('/wood-transactions/sell', [WoodTransactionController::class, 'sell'])->name('wood_transactions.sell');
    Route::post('/wood-transactions/sell', [WoodTransactionController::class, 'storeSell'])->name('wood_transactions.storeSell');

Route::get('/transaksi', [WoodTransactionController::class, 'index'])->name('transaksi.index');
Route::get('/transaksi/{id}', [WoodTransactionController::class, 'show'])->name('transaksi.show');
Route::get('/transaksi/create/{jenis}', function($jenis) {
    return view('transaksi.create', compact('jenis'));
})->name('transaksi.create');


    Route::get('/stock/{woodType}', [App\Http\Controllers\StockController::class, 'detail'])->name('stock.detail');
});