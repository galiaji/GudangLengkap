<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class WoodPriceController extends Controller
{
    public function index(Request $request)
    {
        $woodPrices = DB::table('wood_prices')->get();

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


    public function update(Request $request, $hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        if (!$id) {
            return back()->with('error', 'ID tidak valid');
        }

        $data = [];
        if ($request->has('harga_beli_per_m3')) {
            $data['harga_beli_per_m3'] = (int)str_replace('.', '', $request->harga_beli_per_m3);
        }
        if ($request->has('harga_jual_per_m3')) {
            $data['harga_jual_per_m3'] = (int)str_replace('.', '', $request->harga_jual_per_m3);
        }

        DB::table('wood_prices')->where('id', $id)->update($data);

        return redirect()->route('wood_prices.index')->with('success', 'Harga berhasil diupdate');
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

        DB::table('wood_prices')->where('id', $id)->update([
            'harga_beli_per_m3' => $request->harga_beli_per_m3,
            'updated_at' => now(),
        ]);

        return redirect()->route('wood_prices.index')->with('success', 'Harga beli berhasil diperbarui.');
    }

    public function editJual($hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        $woodPrice = DB::table('wood_prices')->where('id', $id)->first();

        if (!$woodPrice) {
            return redirect()->route('wood_prices.index')->with('error', 'Data tidak ditemukan.');
        }

        return view('wood_prices.edit_jual', compact('woodPrice'));
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

        DB::table('wood_prices')->where('id', $id)->update([
            'harga_jual_per_m3' => $request->harga_jual_per_m3,
            'updated_at' => now(),
        ]);

        return redirect()->route('wood_prices.index')->with('success', 'Harga jual berhasil diperbarui.');
    }
}
