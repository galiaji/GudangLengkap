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
                $totalVolume = WoodStock::where('wood_type_id', $type->id)
                    ->where('grade_id', $grade->id)
                    ->sum('total_volume');
                $gradeVolumes[] = [
                    'grade' => $grade->name,
                    'volume' => (float) $totalVolume,
                ];
            }
            $stockData[$type->name] = $gradeVolumes;
        }

        return view('dashboard', compact('woodTypes', 'grades', 'stockData'));
    }
}
