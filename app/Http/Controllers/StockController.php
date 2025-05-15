<?php

namespace App\Http\Controllers;

use App\Models\WoodType;
use App\Models\WoodStock;
use App\Models\Grade;

class StockController extends Controller
{
    public function detail($woodTypeId)
    {
        $woodType = WoodType::findOrFail($woodTypeId);
        $grades = Grade::all();
        $stocks = WoodStock::where('wood_type_id', $woodTypeId)->get();

        // Kelompokkan stok per grade, diameter, panjang (opsional)
        $grouped = [];
        foreach ($grades as $grade) {
            $grouped[$grade->name] = $stocks->where('grade_id', $grade->id)->groupBy('diameter');
        }

        // Kirim $stocks ke view!
        return view('stock_detail', compact('woodType', 'grades', 'stocks', 'grouped'));
    }
}
