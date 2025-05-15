<?php

namespace App\Http\Controllers;

use App\Models\WoodStock;

class StockController extends Controller
{
    public function detail($woodType)
    {
        // Ambil semua stok berdasarkan nama wood_type (bukan id)
        $stocks = WoodStock::where('wood_type', $woodType)->get();

        // Ambil semua grade unik dari stok (sebagai string)
        $grades = $stocks->pluck('grade')->unique()->values();

        // Kelompokkan stok per grade, diameter, panjang
        $grouped = [];
        foreach ($grades as $grade) {
            $grouped[$grade] = $stocks->where('grade', $grade)->groupBy('diameter');
        }

        $gradeRanges = [
            'Rijek 1' => 'Rijek 10 - 15',
            'Rijek 2' => 'Rijek 16 - 30',
            'Storty 1' => 'Storty 13 - 15',
            'Storty 2' => 'Storty 16 - 18',
            'Super 1' => 'Super 19 - 25',
            'Super 2' => 'Super 25 Up',
        ];

        // Kirim ke view
        return view('stock_detail', compact('woodType', 'grades', 'stocks', 'grouped', 'gradeRanges'));
    }
}
