<?php

namespace App\Http\Controllers;

use App\Models\WoodStock;
use Barryvdh\DomPDF\Facade\Pdf;

class StockExportController extends Controller
{
    public function exportPdf($woodType)
    {
        // Parameter route bisa berupa id atau nama jenis kayu
        $type = \App\Models\WoodType::where('id', $woodType)
            ->orWhere('name', $woodType)
            ->firstOrFail();
        $woodType = $type->name;

        $woodTypes = [$woodType];
        $stocks = WoodStock::with('woodType')->where('wood_type_id', $type->id)->get();

        $pdf = Pdf::loadView('exports.stock_pdf', compact(
            'woodTypes', 'stocks'
        ))->setPaper('a4', 'potrait');

        return $pdf->download('laporan-stok-' . strtolower($woodType) . '.pdf');
    }
}
