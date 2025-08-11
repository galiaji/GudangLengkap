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
        // --- Stok Basah ---
        $wetPallets = PalletStock::with('palletPrice')
            ->where('status', 'basah')
            ->get();

        // --- Stok Penjemuran ---
        $dryingPallets = PalletStock::with('palletPrice')
            ->where('status', 'penjemuran')
            ->get();

        // --- Stok Kering ---
        $dryPallets = PalletStock::with('palletPrice')
            ->where('status', 'kering')
            ->get();

        // --- Rekap per input_code & tanggal untuk BASAH ---
        $groupedWetQuery = PalletStock::select(
                'input_code',
                'tanggal_input',
                DB::raw('SUM(pcs) as total_pcs'),
                DB::raw('SUM(volume) as total_volume')
            )
            ->where('status', 'basah');
        if ($request->filled('tanggal_masuk')) {
            $groupedWetQuery->where('tanggal_input', $request->tanggal_masuk);
        }
        $sortWet = $request->input('sort_wet', 'desc');
        $groupedWetQuery->orderBy('tanggal_input', $sortWet);
        $groupedWet = $groupedWetQuery->groupBy('input_code', 'tanggal_input')->get();

        // --- Rekap per input_code & tanggal untuk PENJEMURAN ---
        $groupedDryingQuery = PalletStock::select(
                'input_code',
                'tanggal_input',
                DB::raw('SUM(pcs) as total_pcs'),
                DB::raw('SUM(volume) as total_volume')
            )
            ->where('status', 'penjemuran');
        if ($request->filled('tanggal_cari')) {
            $groupedDryingQuery->where('tanggal_input', $request->tanggal_cari);
        }
        $sortDrying = $request->input('sort', 'desc');
        $groupedDryingQuery->orderBy('tanggal_input', $sortDrying)
            ->orderBy('input_code', $sortDrying);
        $groupedDrying = $groupedDryingQuery->groupBy('input_code', 'tanggal_input')->get();

        // --- Rekap per input_code & tanggal untuk KERING ---
        $groupedDryQuery = PalletStock::select(
                'input_code',
                'tanggal_input',
                DB::raw('SUM(pcs) as total_pcs'),
                DB::raw('SUM(volume) as total_volume')
            )
            ->where('status', 'kering');
        if ($request->filled('tanggal_kering')) {
            $groupedDryQuery->where('tanggal_input', $request->tanggal_kering);
        }
        $sortDry = $request->input('sort_dry', 'desc');
        $groupedDryQuery->orderBy('tanggal_input', $sortDry)
            ->orderBy('input_code', $sortDry);
        $groupedDry = $groupedDryQuery->groupBy('input_code', 'tanggal_input')->get();

        // --- Detail per input_code ---
        $allWetDetails = PalletStock::where('status', 'basah')->get()->groupBy('input_code');
        $allDryingDetails = PalletStock::where('status', 'penjemuran')->get()->groupBy('input_code');
        $allDryDetails = PalletStock::where('status', 'kering')->get()->groupBy('input_code');

        // --- Summary per pallet_code & ukuran ---
        $wetSummary = PalletStock::select(
                'pallet_code',
                'ukuran',
                DB::raw('SUM(pcs) as pcs'),
                DB::raw('SUM(volume) as volume')
            )
            ->where('status', 'basah')
            ->groupBy('pallet_code', 'ukuran')
            ->get()
            ->map(function($item) {
                $item->harga_basah = PalletPrice::where('pallet_code', $item->pallet_code)->value('harga_basah') ?? 0;
                return $item;
            });

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
                $item->harga_satuan = PalletPrice::where('pallet_code', $item->pallet_code)->value('harga_kering') ?? 0;
                return $item;
            });

        $drySummary = PalletStock::select(
                'pallet_code',
                'ukuran',
                DB::raw('SUM(pcs) as pcs'),
                DB::raw('SUM(volume) as volume')
            )
            ->where('status', 'kering')
            ->groupBy('pallet_code', 'ukuran')
            ->get()
            ->map(function($item) {
                $item->harga_satuan = PalletPrice::where('pallet_code', $item->pallet_code)->value('harga_kering') ?? 0;
                return $item;
            });

        // --- Kirim ke view ---
        return view('stok_palet.index', compact(
            'wetPallets',
            'dryingPallets',
            'dryPallets',
            'groupedWet',
            'groupedDrying',
            'groupedDry',
            'allWetDetails',
            'allDryingDetails',
            'allDryDetails',
            'wetSummary',
            'dryingSummary',
            'drySummary'
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
        // Status: basah, penjemuran, atau kering
        $status = $request->input('status', 'basah');
        $isBasah = $status === 'basah';
        $isDrying = $status === 'penjemuran';
        $isKering = $status === 'kering';

        // Field tanggal sesuai status
        if ($isBasah) {
            $tanggalField = 'entry_date';
            $prefix = 'BSH';
        } elseif ($isDrying) {
            $tanggalField = 'start_drying_date';
            $prefix = 'JMR';
        } else { // kering
            $tanggalField = 'dry_date';
            $prefix = 'KRG';
        }

        // Validasi
        $validated = $request->validate([
            $tanggalField => 'required|date',
            'pallets' => 'required|array',
            'pallets.*.pallet_code' => 'required|exists:pallet_prices,pallet_code',
            'pallets.*.ukuran' => 'required',
            'pallets.*.pcs' => 'nullable|integer|min:0',
            'pallets.*.volume' => 'nullable|numeric|min:0',
        ]);

        $date = date('Ymd', strtotime($request->$tanggalField));
        $last = PalletStock::where('status', $status)
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
        PalletStock::insert($data);

        // Jika request AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data palet berhasil ditambahkan.'
            ]);
        }

        // Jika request biasa, redirect ke halaman stok pallet
        return redirect()->route('pallet_stocks.index')->with('success', 'Data palet berhasil ditambahkan.');
    } else {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang disimpan. Pastikan jumlah diisi.'
            ], 422);
        }
        return redirect()->back()->with('error', 'Tidak ada data yang disimpan. Pastikan jumlah diisi.');
    }
    }

    public function moveToNextStatus($input_code)
    {
        
        $palets = PalletStock::where('input_code', $input_code)->get();

        if ($palets->isEmpty()) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

       
        $currentStatus = $palets->first()->status;
        if ($currentStatus === 'basah') {
            $nextStatus = 'penjemuran';
            $prefix = 'JMR';
        } elseif ($currentStatus === 'penjemuran') {
            $nextStatus = 'kering';
            $prefix = 'KRG';
        } else {
            return back()->with('error', 'Status tidak valid untuk dipindahkan.');
        }

        // Generate kode input baru untuk status berikutnya
        $today = now()->toDateString();
        $date = now()->format('Ymd');
        $last = PalletStock::where('status', $nextStatus)
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
            $palet->status = $nextStatus;
            $palet->input_code = $newInputCode;
            $palet->tanggal_input = $today;
            $palet->save();
        }

        return back()->with('success', 'Palet berhasil dipindahkan ke status ' . $nextStatus . '.');
    }
}