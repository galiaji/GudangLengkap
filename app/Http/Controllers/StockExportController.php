<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WoodStock;
use Barryvdh\DomPDF\Facade\Pdf;

//class StockExportController extends Controller
{
    // public function exportPdf($woodType)
    // {
    //     $woodTypes = [$woodType];
    //     $stocks = \App\Models\WoodStock::where('wood_type', $woodType)->get();

    //     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.stock_pdf', compact(
    //         'woodTypes', 'stocks'
    //     ))->setPaper('a4', 'potrait');

    //     return $pdf->download('laporan-stok-' . strtolower($woodType) . '.pdf');
    // }
    
    // public function exportPalletPdf(Request $request)
    // {
    //     // Ambil semua data pallet, urutkan dan group by status lalu input_code (batch)
    //     $pallets = \App\Models\PalletStock::orderBy('status')
    //         ->orderBy('input_code')
    //         ->get()
    //         ->groupBy(['status', 'input_code']); // Group by status dan input_code (batch)

    //     // Jika ingin rekap total volume per status dan batch:
    //     $rekap = [];
    //     foreach ($pallets as $status => $batches) {
    //         foreach ($batches as $input_code => $items) {
    //             $rekap[$status][$input_code] = [
    //                 'total_volume' => $items->sum('volume'),
    //                 'total_pcs' => $items->sum('pcs'),
    //             ];
    //         }
    //     }

    //     $pdf = Pdf::loadView('exports.pallet_stock_pdf', compact('pallets', 'rekap'))
    //         ->setPaper('a4', 'portrait');

    //     return $pdf->download('laporan-stok-pallet.pdf');
    // }
    
}
