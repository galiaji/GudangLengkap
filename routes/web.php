<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\WoodPriceController;
use App\Http\Controllers\WoodTransactionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PalletPriceController;
use App\Http\Controllers\WetPalletStockController;
use App\Http\Controllers\DryingPalletStockController;
use App\Http\Controllers\PalletStockController;

// Display the login form.
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Semua rute yang membutuhkan autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Hanya admin yang boleh akses harga kayu & akun
    Route::middleware('role:admin')->group(function () {
        Route::get('/wood_prices', [\App\Http\Controllers\WoodPriceController::class, 'index'])->name('wood_prices.index');
        Route::put('wood-prices/{wood_price}', [\App\Http\Controllers\WoodPriceController::class, 'update'])->name('wood_prices.update');
        Route::post('/wood_prices/{id}/update-beli', [\App\Http\Controllers\WoodPriceController::class, 'updateBeli'])->name('wood_prices.updateBeli');
        Route::post('/wood_prices/{id}/update-jual', [\App\Http\Controllers\WoodPriceController::class, 'updateJual'])->name('wood_prices.updateJual');
        Route::resource('account', AccountController::class)->parameters([
            'account' => 'hashid'
        ]);
        Route::resource('pallet-prices', \App\Http\Controllers\PalletPriceController::class)->only(['index', 'edit', 'update']);
    });

    // Semua user login bisa akses transaksi & stok
    Route::get('/transaksi/beli', [WoodTransactionController::class, 'buy'])->name('wood_transactions.buy');
    Route::post('/transaksi/beli', [WoodTransactionController::class, 'storeBuy'])->name('wood_transactions.storeBuy');
    Route::get('/transaksi/jual', [WoodTransactionController::class, 'sell'])->name('wood_transactions.sell');
    Route::post('/transaksi/jual', [WoodTransactionController::class, 'storeSell'])->name('wood_transactions.storeSell');
    Route::get('/transaksi', [WoodTransactionController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{hashid}', [WoodTransactionController::class, 'show'])->name('transaksi.show');
    Route::get('/stock/{woodType}', [App\Http\Controllers\StockController::class, 'detail'])->name('stock.detail');
    Route::get('/transaksi/laporan/harian', [WoodTransactionController::class, 'laporanHarian'])->name('transaksi.laporan.harian');
    Route::get('/transaksi/laporan/bulanan', [WoodTransactionController::class, 'laporanBulanan'])->name('transaksi.laporan.bulanan');
    Route::get('/transaksi/laporan/tahunan', [WoodTransactionController::class, 'laporanTahunan'])->name('transaksi.laporan.tahunan');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    Route::middleware(['auth'])->group(function () {
        Route::get('/profil', function () {
            return view('account.profile');
        })->name('account.profile');
    });

    // Harga Palet
    Route::resource('pallet-prices', PalletPriceController::class)->only(['index', 'edit', 'update']);

    Route::get('pallet-stocks/wet/create', [PalletStockController::class, 'createWet'])->name('pallet_stocks.wet.create');
    Route::post('pallet-stocks/wet', [PalletStockController::class, 'storeWet'])->name('pallet_stocks.wet.store');
    Route::get('pallet-stocks/wet/{id}/edit', [PalletStockController::class, 'editWet'])->name('pallet_stocks.wet.edit');
    Route::put('pallet-stocks/wet/{id}', [PalletStockController::class, 'updateWet'])->name('pallet_stocks.wet.update');
    Route::delete('pallet-stocks/wet/{id}', [PalletStockController::class, 'destroyWet'])->name('pallet_stocks.wet.destroy');

    Route::get('pallet-stocks/drying/create', [PalletStockController::class, 'createDrying'])->name('pallet_stocks.drying.create');
    Route::post('pallet-stocks/drying', [PalletStockController::class, 'storeDrying'])->name('pallet_stocks.drying.store');
    Route::get('pallet-stocks/drying/{id}/edit', [PalletStockController::class, 'editDrying'])->name('pallet_stocks.drying.edit');
    Route::put('pallet-stocks/drying/{id}', [PalletStockController::class, 'updateDrying'])->name('pallet_stocks.drying.update');
    Route::delete('pallet-stocks/drying/{id}', [PalletStockController::class, 'destroyDrying'])->name('pallet_stocks.drying.destroy');

    // Halaman utama stok palet (semua stok)
    Route::get('pallet-stocks', [PalletStockController::class, 'indexStock'])->name('pallet_stocks.index');
});