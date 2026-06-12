<?php

namespace App\Http\Controllers;

use App\Models\WoodStock;
use Barryvdh\DomPDF\Facade\Pdf;

class StockExportController extends Controller
{
    public function exportPdf($woodType)
    {
        $woodTypes = [$woodType];
        $stocks = WoodStock::where('wood_type', $woodType)->get();

        $pdf = Pdf::loadView('exports.stock_pdf', compact(
            'woodTypes', 'stocks'
        ))->setPaper('a4', 'potrait');

        return $pdf->download('laporan-stok-' . strtolower($woodType) . '.pdf');
    }
    
}
