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
use App\Http\Controllers\StockExportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WoodTypeController;

// Display the login form.
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Semua rute yang membutuhkan autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Hanya admin yang boleh akses harga kayu & akun
    Route::middleware('role:admin')->group(function () {
        Route::get('/wood_prices', [\App\Http\Controllers\WoodPriceController::class, 'index'])->name('wood_prices.index');
        // Route::put('wood-prices/{wood_price}', [\App\Http\Controllers\WoodPriceController::class, 'update'])->name('wood_prices.update');
        Route::post('/wood_prices/{id}/update-beli', [\App\Http\Controllers\WoodPriceController::class, 'updateBeli'])->name('wood_prices.updateBeli');
        Route::post('/wood_prices/{id}/update-jual', [\App\Http\Controllers\WoodPriceController::class, 'updateJual'])->name('wood_prices.updateJual');
            
        // Akun (Account) - tanpa resource, satu per satu
        Route::get('account', [AccountController::class, 'index'])->name('account.index');
        Route::get('account/create', [AccountController::class, 'create'])->name('account.create');
        Route::post('account', [AccountController::class, 'store'])->name('account.store');
        Route::get('account/{hashid}/edit', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('account/{hashid}', [AccountController::class, 'update'])->name('account.update');
        Route::delete('account/{hashid}', [AccountController::class, 'destroy'])->name('account.destroy');
        Route::get('pallet-prices/create', [PalletStockController::class, 'createUkuran'])->name('pallet-prices.create');
        Route::post('pallet-prices', [PalletStockController::class, 'storeUkuran'])->name('pallet-prices.store');
        Route::resource('pallet-prices', \App\Http\Controllers\PalletPriceController::class)->only(['index', 'update']);
        Route::get('/stok/rentang/tambah', [StockController::class, 'createRange'])->name('stok.range.create');
        Route::post('/stok/rentang/simpan', [StockController::class, 'storeRange'])->name('stok.range.store');
        Route::get('/wood-types', [WoodTypeController::class, 'index'])->name('wood-types.index');

        Route::post('/wood-types', [WoodTypeController::class, 'store'])->name('wood-types.store');
        Route::delete('/wood-types/{woodType}', [WoodTypeController::class, 'destroy'])->name('wood-types.destroy');
        Route::patch('/wood-types/{woodType}/hide', [WoodTypeController::class, 'hide'])->name('wood-types.hide');
        Route::patch('/wood-types/{woodType}/unhide', [WoodTypeController::class, 'unhide'])->name('wood-types.unhide');
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

  
    // Stok Palet (semua status: basah & penjemuran)
    Route::get('pallet-stocks/create', [PalletStockController::class, 'create'])->name('pallet_stocks.create');
    Route::post('pallet-stocks', [PalletStockController::class, 'store'])->name('pallet_stocks.store');

    // Halaman utama stok palet (semua stok)
    Route::get('pallet-stocks', [PalletStockController::class, 'indexStock'])->name('pallet_stocks.index');
    Route::post('/stok-palet/move-to-next-status/{input_code}', [PalletStockController::class, 'moveToNextStatus'])->name('stok_palet.moveToNextStatus');

    Route::get('pallet-sales', [\App\Http\Controllers\PalletSaleController::class, 'index'])->name('pallet-sales.index');
    Route::get('pallet-sales/create', [\App\Http\Controllers\PalletSaleController::class, 'create'])->name('pallet-sales.create');
    Route::post('pallet-sales', [\App\Http\Controllers\PalletSaleController::class, 'store'])->name('pallet-sales.store');
    // Route::get('pallet-sales/{pallet_sale}', [\App\Http\Controllers\PalletSaleController::class, 'show'])->name('pallet-sales.show');
    Route::get('pallet-sales/{id}/nota', [\App\Http\Controllers\PalletSaleController::class, 'exportNota'])->name('pallet-sales.nota');
    Route::get('/pallet-sales/laporan-mingguan', [\App\Http\Controllers\PalletSaleController::class, 'laporanMingguan'])->name('pallet-sales.laporan-mingguan');

    Route::get('/export-stok-pdf/{woodType}', [StockExportController::class, 'exportPdf'])->name('export.stok.pdf');
    Route::get('/stock/potong-gergaji/{woodType}', [\App\Http\Controllers\StockController::class, 'showPotongGergajiForm'])->name('stock.potongGergaji.form');
    Route::post('/wood-transactions/store-saw', [\App\Http\Controllers\StockController::class, 'storeSaw'])->name('wood_transactions.storeSaw');
});