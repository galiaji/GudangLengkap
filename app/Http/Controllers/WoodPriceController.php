<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WoodPrice;
use Vinkla\Hashids\Facades\Hashids;

class WoodPriceController extends Controller
{
    public function index(Request $request)
    {
        // Menggunakan Eloquent model WoodPrice
        $woodPrices = WoodPrice::all();

        // Mapping grade ke rentang
        $gradeRanges = [
            'Rijek 1' => 'Rijek 10 - 15',
            'Rijek 2' => 'Rijek 16 - 30',
            'Storty 1' => 'Storty 13 - 15',
            'Storty 2' => 'Storty 16 - 18',
            'Super 1' => 'Super 19 - 25 ',
            'Super 2' => 'Super 25 Up',
        ];

        // Ambil tipe harga dari query string, default 'all'
        $tipeHarga = $request->query('tipe', 'all'); // 'beli', 'jual', atau 'all'

        return view('wood_prices.index', compact('woodPrices', 'gradeRanges', 'tipeHarga'));
    }

  
    public function updateBeli(Request $request, $hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        if (!$id) {
            return redirect()->route('wood_prices.index')->with('error', 'Data tidak ditemukan.');
        }

        $request->validate([
            'harga_beli_per_m3' => 'required|numeric',
        ]);

        $woodPrice = WoodPrice::find($id);
        if (!$woodPrice) {
            return redirect()->route('wood_prices.index')->with('error', 'Data tidak ditemukan.');
        }

        $woodPrice->harga_beli_per_m3 = $request->harga_beli_per_m3;
        $woodPrice->save();

        return redirect()->route('wood_prices.index')->with('success', 'Harga beli berhasil diperbarui.');
    }


    public function updateJual(Request $request, $hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        if (!$id) {
            return redirect()->route('wood_prices.index')->with('error', 'Data tidak ditemukan.');
        }

        $request->validate([
            'harga_jual_per_m3' => 'required|numeric',
        ]);

        $woodPrice = WoodPrice::find($id);
        if (!$woodPrice) {
            return redirect()->route('wood_prices.index')->with('error', 'Data tidak ditemukan.');
        }

        $woodPrice->harga_jual_per_m3 = $request->harga_jual_per_m3;
        $woodPrice->save();

        return redirect()->route('wood_prices.index')->with('success', 'Harga jual berhasil diperbarui.');
    }
    public function getHargaPerM3()
    {
        $allPrices = WoodPrice::all();
        $hargaPerM3 = [];

        foreach ($allPrices as $row) {
            $hargaPerM3[$row->wood_type][$row->grade][$row->panjang] = [
                'beli' => $row->harga_beli_per_m3,
                'jual' => $row->harga_jual_per_m3,
            ];
        }

        return $hargaPerM3;
    }
}
