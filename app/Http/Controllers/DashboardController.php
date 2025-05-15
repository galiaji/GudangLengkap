<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WoodStock;
use App\Models\WoodType;
use App\Models\Grade;

class DashboardController extends Controller
{
  public function index()
{
    $woodTypes = WoodType::all();
    $grades = Grade::all();

    // Ambil data stok dan kelompokkan per jenis kayu dan grade
    $stockData = [];
    foreach ($woodTypes as $type) {
        $gradeVolumes = [];
        foreach ($grades as $grade) {
            // Hitung total volume untuk jenis kayu dan grade tertentu
            $totalVolume = WoodStock::where('wood_type_id', $type->id)
                ->where('grade_id', $grade->id)
                ->sum('total_volume');

            // Hitung total pcs untuk jenis kayu dan grade tertentu
            $totalPcs = WoodStock::where('wood_type_id', $type->id)
                ->where('grade_id', $grade->id)
                ->sum('quantity');

            $gradeVolumes[] = [
                'grade' => $grade->name,
                'pcs'   => (int) $totalPcs,
                'volume'=> (float) $totalVolume,
            ];
        }
        $stockData[$type->name] = $gradeVolumes;
    }

    return view('dashboard', compact('woodTypes', 'grades', 'stockData'));
}
}
