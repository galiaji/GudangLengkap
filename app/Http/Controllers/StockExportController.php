<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WoodStock;
use Barryvdh\DomPDF\Facade\Pdf;

class StockExportController extends Controller
{
    public function exportPdf($woodType)
    {
        $woodTypes = [$woodType];
        $stocks = \App\Models\WoodStock::where('wood_type', $woodType)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.stock_pdf', compact(
            'woodTypes', 'stocks'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-' . strtolower($woodType) . '.pdf');
    }
}
