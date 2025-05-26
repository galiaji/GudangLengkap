<?php

namespace App\Http\Controllers;

use App\Models\WetPalletStock;
use App\Models\DryingPalletStock;
use App\Models\PalletPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PalletStockController extends Controller
{
    // Menampilkan rangkuman, stok basah, dan stok penjemuran
    public function indexStock(Request $request)
    {
        $wetPallets = WetPalletStock::with('palletPrice')->get();
        $dryingPallets = DryingPalletStock::with('palletPrice')->get();

        // Rekap per kode jemur
        $groupedDryingQuery = DryingPalletStock::select(
                'drying_code',
                'start_drying_date',
                DB::raw('SUM(pcs) as total_pcs'),
                DB::raw('SUM(volume) as total_volume')
            )
            ->groupBy('drying_code', 'start_drying_date');

        // Filter & sort hanya untuk groupedDrying
        if ($request->filled('tanggal_cari')) {
            $groupedDryingQuery->where('start_drying_date', $request->tanggal_cari);
        }
        $sort = $request->input('sort', 'desc');
        $groupedDryingQuery->orderBy('start_drying_date', $sort)
            ->orderBy('drying_code', $sort);

        $groupedDrying = $groupedDryingQuery->get();
        $dryingSummary = \App\Models\DryingPalletStock::select(
            'pallet_code',
            'ukuran',
            DB::raw('SUM(pcs) as pcs'),
            DB::raw('SUM(volume) as volume')
        )
        ->groupBy('pallet_code', 'ukuran')
        ->get()
        ->map(function($item) {
            // Ambil harga dari kolom 'harga_kering'
            $item->harga_satuan = \App\Models\PalletPrice::where('pallet_code', $item->pallet_code)->value('harga_kering') ?? 0;
            return $item;
        });

        // Ambil semua detail penjemuran, group by drying_code
        $allDetails = \App\Models\DryingPalletStock::all()->groupBy('drying_code');
        return view('stok_palet.index', compact('wetPallets', 'dryingPallets', 'dryingSummary', 'groupedDrying', 'allDetails'));
    }

    // Form tambah stok palet basah
    public function createWet(){
        $palletPrices = PalletPrice::all();
        return view('stok_palet.createWet', compact('palletPrices'));
    }

    // Simpan stok palet basah
    public function storeWet(Request $request){
        $request->validate([
            'pallet_code' => 'required|exists:pallet_prices,pallet_code',
            'ukuran' => 'required',
            'pcs' => 'required|integer|min:1',
            'volume' => 'required|numeric|min:0',
            'entry_date' => 'required|date',
        ]);

        WetPalletStock::create($request->all());
        return redirect()->route('pallet_stocks.wet.index')->with('success', 'Palet basah berhasil ditambahkan.');
    }

    // Form tambah stok palet penjemuran
    public function createDrying(){
        $palletPrices = PalletPrice::all();
        return view('stok_palet.drying_create', compact('palletPrices'));
    }

    // Simpan stok palet penjemuran
    public function storeDrying(Request $request){
        $request->validate([
            'start_drying_date' => 'required|date',
            'pallets' => 'required|array',
            'pallets.*.pallet_code' => 'required|exists:pallet_prices,pallet_code',
            'pallets.*.ukuran' => 'required',
            'pallets.*.pcs' => 'nullable|integer|min:0',
            'pallets.*.volume' => 'nullable|numeric|min:0',
        ]);

        // Generate drying_code, contoh: JMR20250526-001
        $date = date('Ymd', strtotime($request->start_drying_date));
        $last = \App\Models\DryingPalletStock::where('start_drying_date', $request->start_drying_date)
            ->orderByDesc('drying_code')
            ->first();
        $urut = 1;
        if ($last && preg_match('/-(\d+)$/', $last->drying_code, $m)) {
            $urut = intval($m[1]) + 1;
        }
        $drying_code = 'JMR' . $date . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

        $data = [];
        foreach ($request->pallets as $pallet) {
            if (!empty($pallet['pcs']) && $pallet['pcs'] > 0) {
                $data[] = [
                    'pallet_code' => $pallet['pallet_code'],
                    'ukuran' => $pallet['ukuran'],
                    'pcs' => $pallet['pcs'],
                    'volume' => $pallet['volume'],
                    'start_drying_date' => $request->start_drying_date,
                    'drying_code' => $drying_code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($data)) {
            \App\Models\DryingPalletStock::insert($data);
            return redirect()->route('pallet_stocks.index')->with('success', 'Data palet penjemuran berhasil disimpan.');
        } else {
            return redirect()->back()->with('error', 'Tidak ada data yang disimpan. Pastikan jumlah diisi.');
        }
    }
}