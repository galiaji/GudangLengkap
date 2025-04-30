<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\WoodPriceController;

Route::get('/wood-prices', [WoodPriceController::class, 'index'])->name('wood_prices.index');
Route::get('/wood-prices/{id}/edit', [WoodPriceController::class, 'edit'])->name('wood_prices.edit');
Route::put('/wood-prices/{id}', [WoodPriceController::class, 'update'])->name('wood_prices.update');