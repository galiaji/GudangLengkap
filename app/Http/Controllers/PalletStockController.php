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
        // Semua ukuran terdaftar di harga palet selalu tampil (0 jika belum ada stok).
        $prices = PalletPrice::orderBy('id')->get();
        $wetSummary    = $this->buildSummary($prices, 'basah', 'harga_basah', 'harga_basah');
        $dryingSummary = $this->buildSummary($prices, 'penjemuran', 'harga_kering', 'harga_satuan');
        $drySummary    = $this->buildSummary($prices, 'kering', 'harga_kering', 'harga_satuan');

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

    /**
     * Ringkasan stok per ukuran untuk satu status.
     * $priceColumn: kolom harga yang dipakai, $priceKey: nama properti yang dibaca view.
     */
    private function buildSummary($prices, string $status, string $priceColumn, string $priceKey)
    {
        $totals = PalletStock::select(
                'pallet_code',
                'ukuran',
                DB::raw('SUM(pcs) as pcs'),
                DB::raw('SUM(volume) as volume')
            )
            ->where('status', $status)
            ->groupBy('pallet_code', 'ukuran')
            ->get()
            ->keyBy(fn ($row) => $row->pallet_code . '|' . $row->ukuran);

        $summary = $prices->map(function ($price) use ($totals, $priceColumn, $priceKey) {
            $row = $totals->pull($price->pallet_code . '|' . $price->ukuran);
            return (object) [
                'pallet_code' => $price->pallet_code,
                'ukuran'      => $price->ukuran,
                'pcs'         => (int) ($row->pcs ?? 0),
                'volume'      => (float) ($row->volume ?? 0),
                $priceKey     => (float) ($price->$priceColumn ?? 0),
            ];
        });

        // Stok dengan ukuran yang tidak ada di daftar harga tetap ditampilkan
        foreach ($totals as $row) {
            $summary->push((object) [
                'pallet_code' => $row->pallet_code,
                'ukuran'      => $row->ukuran,
                'pcs'         => (int) $row->pcs,
                'volume'      => (float) $row->volume,
                $priceKey     => 0,
            ]);
        }

        return $summary->values();
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

        // Tentukan prefix sesuai status
        $prefix = match($status) {
            'basah'      => 'BSH',
            'penjemuran' => 'JMR',
            'kering'     => 'KRG',
            default      => 'BSH',
        };

        $validated = $request->validate([
            'status' => 'nullable|in:basah,penjemuran,kering',
            'tanggal_input' => 'required|date',
            'pallets' => 'required|array',
            'pallets.*.pallet_code' => 'required|exists:pallet_prices,pallet_code',
            'pallets.*.ukuran' => 'required',
            'pallets.*.pcs' => 'nullable|integer|min:0',
            'pallets.*.volume' => 'nullable|numeric|min:0',
        ]);

        // Buat kode input unik
        $date = date('Ymd', strtotime($request->tanggal_input));
        $last = PalletStock::where('status', $status)
            ->where('tanggal_input', $request->tanggal_input)
            ->orderByDesc('input_code')
            ->first();

        $urut = 1;
        if ($last && preg_match('/-(\d+)$/', $last->input_code, $m)) {
            $urut = intval($m[1]) + 1;
        }

        $input_code = $prefix . $date . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

        // Siapkan data insert
        // Ukuran & volume diambil dari data palet (berdasarkan kode), bukan dari browser
        $palletPrices = PalletPrice::whereIn('pallet_code', collect($request->pallets)->pluck('pallet_code'))
            ->get()->keyBy('pallet_code');

        $data = [];
        foreach ($request->pallets as $pallet) {
            if (!empty($pallet['pcs']) && $pallet['pcs'] > 0) {
                $price = $palletPrices[$pallet['pallet_code']];
                $data[] = [
                    'pallet_code'   => $pallet['pallet_code'],
                    'ukuran'        => $price->ukuran,
                    'pcs'           => $pallet['pcs'],
                    'volume'        => round($pallet['pcs'] * $price->volume_per_pcs, 3),
                    'status'        => $status,
                    'input_code'    => $input_code,
                    'tanggal_input' => $request->tanggal_input, 
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        // Simpan data
        if (!empty($data)) {
            PalletStock::insert($data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data palet berhasil ditambahkan.'
                ]);
            }

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

    // Form tambah ukuran palet baru
    public function createUkuran()
    {
        $existing = PalletPrice::orderBy('id')->pluck('ukuran');
        $nextCode = PalletPrice::nextCode();
        return view('stok_palet.createHarga', compact('existing', 'nextCode'));
    }

    // Simpan ukuran palet baru (harga default 0)
    public function storeUkuran(Request $request)
    {
        $request->validate([
            'tebal'   => 'required|numeric|gt:0|max:1000',
            'lebar'   => 'required|numeric|gt:0|max:1000',
            'panjang' => 'required|numeric|gt:0|max:10000',
        ], [], [
            'tebal'   => 'tebal',
            'lebar'   => 'lebar',
            'panjang' => 'panjang',
        ]);

        $ukuran = PalletPrice::formatUkuran($request->tebal, $request->lebar, $request->panjang);

        if (PalletPrice::where('ukuran', $ukuran)->exists()) {
            return back()->withInput()->withErrors([
                'tebal' => "Ukuran {$ukuran} sudah terdaftar.",
            ]);
        }

        $palletPrice = PalletPrice::create([
            'pallet_code'    => PalletPrice::nextCode(),
            'ukuran'         => $ukuran,
            'volume_per_pcs' => PalletPrice::volumeFromUkuran($ukuran),
            'harga_basah'    => 0,
            'harga_kering'   => 0,
        ]);

        return redirect()
            ->route('pallet-prices.index')
            ->with('success', "Ukuran palet {$ukuran} (kode {$palletPrice->pallet_code}) berhasil ditambahkan. Harga masih 0, silakan isi harganya.")
            ->with('new_id', $palletPrice->id);
    }
}