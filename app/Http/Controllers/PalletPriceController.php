<?php

namespace App\Http\Controllers;

use App\Models\PalletPrice;
use App\Models\PalletSaleItem;
use App\Models\PalletStock;
use Illuminate\Http\Request;

class PalletPriceController extends Controller
{
    // Tampilkan daftar harga palet
    public function index()
    {
        $palletPrices = PalletPrice::orderBy('id')->get();
        return view('stok_palet.indexHarga', compact('palletPrices'));
    }

    /**
     * Hapus ukuran palet. Ditolak jika ukuran masih punya stok atau riwayat penjualan,
     * karena nota penjualan disusun dari daftar ukuran ini.
     */
    public function destroy($id)
    {
        $palletPrice = PalletPrice::findOrFail($id);
        $ukuran      = $palletPrice->ukuran;

        $dipakai = function ($model) use ($palletPrice) {
            return $model::where('pallet_code', $palletPrice->pallet_code)
                ->orWhere('ukuran', $palletPrice->ukuran)
                ->exists();
        };

        if ($dipakai(PalletStock::class)) {
            return redirect()->route('pallet-prices.index')
                ->with('error', "Ukuran {$ukuran} tidak bisa dihapus karena masih ada stok palet dengan ukuran ini.");
        }
        if ($dipakai(PalletSaleItem::class)) {
            return redirect()->route('pallet-prices.index')
                ->with('error', "Ukuran {$ukuran} tidak bisa dihapus karena sudah dipakai pada transaksi penjualan.");
        }

        $palletPrice->delete();

        return redirect()->route('pallet-prices.index')->with('success', "Ukuran {$ukuran} berhasil dihapus.");
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

        try {
            $palletPrice = PalletPrice::findOrFail($id);
            $palletPrice->update([
                'harga_basah' => $harga_basah,
                'harga_kering' => $harga_kering,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Harga palet berhasil diperbarui.',
                    'redirect_url' => route('pallet-prices.index')
                ]);
            }
            return redirect()->route('pallet-prices.index')->with('success', 'Harga palet berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui harga palet: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui harga palet: ' . $e->getMessage());
        }
    }
}