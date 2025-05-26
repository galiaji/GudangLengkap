<?php

namespace App\Http\Controllers;

use App\Models\PalletPrice;
use App\Models\PalletStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PalletStockController extends Controller
{
    // Menampilkan rangkuman, stok basah, dan stok penjemuran
    public function indexStock(Request $request)
    {
        // Ambil stok basah
        $wetPallets = PalletStock::with('palletPrice')
            ->where('status', 'basah')
            ->get();

        // Ambil stok penjemuran
        $dryingPallets = PalletStock::with('palletPrice')
            ->where('status', 'penjemuran')
            ->get();

        // Rekap per kode jemur (input_code)
        $groupedDryingQuery = PalletStock::select(
                'input_code',
                'tanggal_input',
                DB::raw('SUM(pcs) as total_pcs'),
                DB::raw('SUM(volume) as total_volume')
            )
            ->where('status', 'penjemuran')
            ->groupBy('input_code', 'tanggal_input');

        // Filter & sort hanya untuk groupedDrying
        if ($request->filled('tanggal_cari')) {
            $groupedDryingQuery->where('tanggal_input', $request->tanggal_cari);
        }
        $sort = $request->input('sort', 'desc');
        $groupedDryingQuery->orderBy('tanggal_input', $sort)
            ->orderBy('input_code', $sort);

        $groupedDrying = $groupedDryingQuery->get();

        // Rekap per pallet_code & ukuran untuk penjemuran
        $dryingSummary = PalletStock::select(
                'pallet_code',
                'ukuran',
                DB::raw('SUM(pcs) as pcs'),
                DB::raw('SUM(volume) as volume')
            )
            ->where('status', 'penjemuran')
            ->groupBy('pallet_code', 'ukuran')
            ->get()
            ->map(function($item) {
                $item->harga_satuan = \App\Models\PalletPrice::where('pallet_code', $item->pallet_code)->value('harga_kering') ?? 0;
                return $item;
            });

        // Ambil semua detail penjemuran, group by input_code
        $allDetails = PalletStock::where('status', 'penjemuran')->get()->groupBy('input_code');

        $query = \App\Models\PalletStock::select(
                'input_code',
                'tanggal_input',
                DB::raw('SUM(pcs) as total_pcs'),
                DB::raw('SUM(volume) as total_volume')
            )
            ->where('status', 'basah');

        if ($request->filled('tanggal_masuk')) {
            $query->where('tanggal_input', $request->tanggal_masuk);
        }

        $sortWet = $request->input('sort_wet', 'desc');
        $query->orderBy('tanggal_input', $sortWet);

        $groupedWet = $query->groupBy('input_code', 'tanggal_input')->get();

        $allWetDetails = \App\Models\PalletStock::where('status', 'basah')->get()->groupBy('input_code');

        // Group dan summary stok basah
        $wetSummary = \App\Models\PalletStock::select(
                'pallet_code',
                'ukuran',
                DB::raw('SUM(pcs) as pcs'),
                DB::raw('SUM(volume) as volume')
            )
            ->where('status', 'basah')
            ->groupBy('pallet_code', 'ukuran')
            ->get()
            ->map(function($item) {
                $item->harga_basah = \App\Models\PalletPrice::where('pallet_code', $item->pallet_code)->value('harga_basah') ?? 0;
                return $item;
            });

        return view('stok_palet.index', compact(
            'wetPallets',
            'dryingPallets',
            'dryingSummary',
            'groupedDrying',
            'allDetails',
            'groupedWet',
            'allWetDetails',
            'wetSummary'
        ));
    }

    // Form tambah stok palet (basah/penjemuran)
    public function create()
    {
        $palletPrices = PalletPrice::all();
        return view('stok_palet.create', compact('palletPrices'));
    }

    // Simpan stok palet (basah/penjemuran)
    public function store(Request $request)
    {
        $status = $request->input('status', 'basah');
        $isBasah = $status === 'basah';

        $tanggalField = $isBasah ? 'entry_date' : 'start_drying_date';

        $request->validate([
            $tanggalField => 'required|date',
            'pallets' => 'required|array',
            'pallets.*.pallet_code' => 'required|exists:pallet_prices,pallet_code',
            'pallets.*.ukuran' => 'required',
            'pallets.*.pcs' => 'nullable|integer|min:0',
            'pallets.*.volume' => 'nullable|numeric|min:0',
        ]);

        $date = date('Ymd', strtotime($request->$tanggalField));
        $prefix = $isBasah ? 'BSH' : 'JMR';
        $last = \App\Models\PalletStock::where('status', $status)
            ->where('tanggal_input', $request->$tanggalField)
            ->orderByDesc('input_code')
            ->first();
        $urut = 1;
        if ($last && preg_match('/-(\d+)$/', $last->input_code, $m)) {
            $urut = intval($m[1]) + 1;
        }
        $input_code = $prefix . $date . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

        $data = [];
        foreach ($request->pallets as $pallet) {
            if (!empty($pallet['pcs']) && $pallet['pcs'] > 0) {
                $data[] = [
                    'pallet_code'   => $pallet['pallet_code'],
                    'ukuran'        => $pallet['ukuran'],
                    'pcs'           => $pallet['pcs'],
                    'volume'        => $pallet['volume'],
                    'status'        => $status,
                    'input_code'    => $input_code,
                    'tanggal_input' => $request->$tanggalField,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        if (!empty($data)) {
            \App\Models\PalletStock::insert($data);
            return redirect()->route('pallet_stocks.index')->with('success', 'Data palet berhasil ditambahkan.');
        } else {
            return redirect()->back()->with('error', 'Tidak ada data yang disimpan. Pastikan jumlah diisi.');
        }
    }

    public function moveToDrying($input_code)
    {
        // Ambil semua palet basah dengan input_code terkait
        $palets = \App\Models\PalletStock::where('input_code', $input_code)
            ->where('status', 'basah')
            ->get();

        if ($palets->isEmpty()) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        // Generate kode input baru untuk penjemuran (JMR + tanggal hari ini + urut)
        $today = now()->toDateString();
        $date = now()->format('Ymd');
        $prefix = 'JMR';
        $last = \App\Models\PalletStock::where('status', 'penjemuran')
            ->where('tanggal_input', $today)
            ->orderByDesc('input_code')
            ->first();
        $urut = 1;
        if ($last && preg_match('/-(\d+)$/', $last->input_code, $m)) {
            $urut = intval($m[1]) + 1;
        }
        $newInputCode = $prefix . $date . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

        // Update status, input_code, dan tanggal_input ke hari ini
        foreach ($palets as $palet) {
            $palet->status = 'penjemuran';
            $palet->input_code = $newInputCode;
            $palet->tanggal_input = $today;
            $palet->save();
        }

        return back()->with('success', 'Palet berhasil dipindahkan ke penjemuran.');
    }
}