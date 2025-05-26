<?php

namespace App\Http\Controllers;

use App\Models\PalletPrice;
use Illuminate\Http\Request;

class PalletPriceController extends Controller
{
    // Tampilkan daftar harga palet
    public function index()
    {
        $palletPrices = PalletPrice::all();
        return view('stok_palet.indexHarga', compact('palletPrices'));
    }

    // Tampilkan form edit harga palet
    public function edit($id)
    {
        $palletPrice = PalletPrice::findOrFail($id);
        return view('stok_palet.edit', compact('palletPrice'));
    }

    // Update harga palet
    public function update(Request $request, $id)
    {
        // Hilangkan semua karakter selain angka
        $harga_basah = preg_replace('/[^\d]/', '', $request->harga_basah);
        $harga_kering = preg_replace('/[^\d]/', '', $request->harga_kering);

        $request->merge([
            'harga_basah' => $harga_basah,
            'harga_kering' => $harga_kering,
        ]);

        $request->validate([
            'harga_basah' => 'required|numeric|min:0',
            'harga_kering' => 'required|numeric|min:0',
        ]);

        $palletPrice = PalletPrice::findOrFail($id);
        $palletPrice->update([
            'harga_basah' => $harga_basah,
            'harga_kering' => $harga_kering,
        ]);

        return redirect()->route('pallet-prices.index')->with('success', 'Harga palet berhasil diperbarui.');
    }
}