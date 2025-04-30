<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WoodPriceController extends Controller
{
    public function index()
    {
        $woodPrices = DB::table('wood_prices')
            ->join('wood_types', 'wood_prices.wood_type_id', '=', 'wood_types.id')
            ->join('grades', 'wood_prices.grade_id', '=', 'grades.id')
            ->select('wood_prices.*', 'wood_types.name as wood_type_name', 'grades.name as grade_name')
            ->get();

        return view('wood_prices.index', compact('woodPrices'));
    }

    public function edit($id)
    {
        $woodPrice = DB::table('wood_prices')
            ->join('wood_types', 'wood_prices.wood_type_id', '=', 'wood_types.id')
            ->join('grades', 'wood_prices.grade_id', '=', 'grades.id')
            ->select('wood_prices.*', 'wood_types.name as wood_type_name', 'grades.name as grade_name')
            ->where('wood_prices.id', $id)
            ->first();

        if (!$woodPrice) {
            return redirect()->route('wood_prices.index')->with('error', 'Data tidak ditemukan.');
        }

        return view('wood_prices.edit', compact('woodPrice'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'price_per_m3' => 'required|numeric', // Validasi sebagai angka
        ]);

        // Perbarui hanya kolom harga
        DB::table('wood_prices')->where('id', $id)->update([
            'price_per_m3' => $request->price_per_m3,
            'updated_at' => now(),
        ]);

        return redirect()->route('wood_prices.index')->with('success', 'Harga kayu berhasil diperbarui.');
    }
}
