<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\WoodPriceController;

Route::get('/wood-prices', [WoodPriceController::class, 'index'])->name('wood_prices.index');
Route::get('/wood-prices/{id}/edit', [WoodPriceController::class, 'edit'])->name('wood_prices.edit');
Route::put('/wood-prices/{id}', [WoodPriceController::class, 'update'])->name('wood_prices.update');

use App\Http\Controllers\WoodTransactionController;

Route::get('/wood-transactions/buy', [WoodTransactionController::class, 'buy'])->name('wood_transactions.buy');
Route::post('/wood-transactions/buy', [WoodTransactionController::class, 'storeBuy'])->name('wood_transactions.storeBuy');

Route::get('/wood-transactions/sell', [WoodTransactionController::class, 'sell'])->name('wood_transactions.sell');
Route::post('/wood-transactions/sell', [WoodTransactionController::class, 'storeSell'])->name('wood_transactions.storeSell');