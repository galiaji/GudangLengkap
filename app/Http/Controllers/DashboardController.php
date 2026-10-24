<?php

namespace App\Http\Controllers;

use App\Models\WoodStock;
use App\Models\WoodType;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Urutan grade sama dengan laporan PDF stok
    private const GRADE_ORDER = ['Storty 1', 'Storty 2', 'Super 1', 'Super 2', 'Rijek 1', 'Rijek 2'];

    public function index()
    {
        $woodTypes = WoodType::visible()->orderBy('name')->get();

        $gradeRanges = [
            'Rijek 1'  => 'Rijek 10 - 15',
            'Rijek 2'  => 'Rijek 16 - 30',
            'Storty 1' => 'Storty 13 - 15',
            'Storty 2' => 'Storty 16 - 18',
            'Super 1'  => 'Super 19 - 25',
            'Super 2'  => 'Super 25 Up',
        ];

        // Grade yang ada di stok, diurutkan; grade lain (jika ada) di belakang
        $grades = WoodStock::distinct()->pluck('grade')
            ->sortBy(function ($g) {
                $i = array_search($g, self::GRADE_ORDER, true);
                return $i === false ? 100 . $g : sprintf('%03d', $i);
            })
            ->values();

        // Satu query: total pcs & volume per jenis kayu per grade
        $totals = WoodStock::select(
                'wood_type_id',
                'grade',
                DB::raw('SUM(quantity) as pcs'),
                DB::raw('SUM(total_volume) as volume')
            )
            ->whereIn('wood_type_id', $woodTypes->pluck('id'))
            ->groupBy('wood_type_id', 'grade')
            ->get()
            ->keyBy(fn ($row) => $row->wood_type_id . '|' . $row->grade);

        $stockData = [];
        $grandPcs = 0;
        $grandVolume = 0.0;

        foreach ($woodTypes as $woodType) {
            $rows = [];
            $sumPcs = 0;
            $sumVolume = 0.0;

            foreach ($grades as $grade) {
                $t = $totals->get($woodType->id . '|' . $grade);
                $pcs = (int) ($t->pcs ?? 0);
                $volume = round((float) ($t->volume ?? 0), 3);

                $rows[] = [
                    'grade'  => $grade,
                    'label'  => $gradeRanges[$grade] ?? $grade,
                    'pcs'    => $pcs,
                    'volume' => $volume,
                ];
                $sumPcs += $pcs;
                $sumVolume += $volume;
            }

            // Persentase volume tiap grade terhadap total jenis kayu ini
            foreach ($rows as &$row) {
                $row['persen'] = $sumVolume > 0 ? round($row['volume'] / $sumVolume * 100, 1) : 0;
            }
            unset($row);

            $stockData[$woodType->id] = [
                'name'         => $woodType->name,
                'grades'       => $rows,
                'total_pcs'    => $sumPcs,
                'total_volume' => round($sumVolume, 3),
            ];

            $grandPcs += $sumPcs;
            $grandVolume += $sumVolume;
        }

        $grandVolume = round($grandVolume, 3);

        return view(
            'dashboard',
            compact(
                'woodTypes',
                'grades',
                'stockData',
                'gradeRanges',
                'grandPcs',
                'grandVolume'
            )
        );
    }
}
