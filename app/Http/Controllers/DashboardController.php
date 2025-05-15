<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WoodStock;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil semua jenis kayu unik
        $woodTypes = WoodStock::pluck('wood_type')->unique()->values();
        // Ambil semua grade unik
        $grades = WoodStock::pluck('grade')->unique()->values();

        // Ambil data stok dan kelompokkan per jenis kayu dan grade
        $stockData = [];
        foreach ($woodTypes as $type) {
            $gradeVolumes = [];
            foreach ($grades as $grade) {
                // Hitung total volume dan pcs untuk jenis kayu dan grade tertentu
                $totalVolume = WoodStock::where('wood_type', $type)
                    ->where('grade', $grade)
                    ->sum('total_volume');
                $totalPcs = WoodStock::where('wood_type', $type)
                    ->where('grade', $grade)
                    ->sum('quantity');
                $gradeVolumes[] = [
                    'grade' => $grade,
                    'pcs'   => (int) $totalPcs,
                    'volume'=> (float) $totalVolume,
                ];
            }
            $stockData[$type] = $gradeVolumes;
        }

        $gradeRanges = [
            'Rijek 1' => 'Rijek 10 - 15',
            'Rijek 2' => 'Rijek 16 - 30',
            'Storty 1' => 'Storty 13 - 15',
            'Storty 2' => 'Storty 16 - 18',
            'Super 1' => 'Super 19 - 25',
            'Super 2' => 'Super 25 Up',
        ];

        return view('dashboard', compact('woodTypes', 'grades', 'stockData', 'gradeRanges'));
    }
}
