<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class WoodPriceController extends Controller
{
    public function index()
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

        return view('wood_prices.index', compact('woodPrices', 'gradeRanges'));
    }

    public function edit($hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        $woodPrice = DB::table('wood_prices')->where('id', $id)->first();

        if (!$woodPrice) {
            return redirect()->route('wood_prices.index')->with('error', 'Data tidak ditemukan.');
        }

        return view('wood_prices.edit', compact('woodPrice'));
    }

    public function update(Request $request, $hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        if (!$id) {
            return redirect()->route('wood_prices.index')->with('error', 'Data tidak ditemukan.');
        }

        $request->validate([
            'price_per_m3' => 'required|numeric',
        ]);

        DB::table('wood_prices')->where('id', $id)->update([
            'price_per_m3' => $request->price_per_m3,
            'updated_at' => now(),
        ]);

        return redirect()->route('wood_prices.index')->with('success', 'Harga kayu berhasil diperbarui.');
    }
}
