<?php

namespace App\Http\Controllers;

use App\Models\PalletSale;
use App\Models\PalletSaleItem;
use App\Models\PalletPrice;
use App\Models\PalletStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // Tambahkan di bagian atas jika belum ada

class PalletSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = PalletSale::with('items');

        if ($request->filled('customer')) {
            $query->where('customer', 'like', '%' . $request->customer . '%');
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Sorting
        $sort = $request->get('sort', 'tanggal');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $sales = $query->paginate(10)->appends($request->all());

        return view('pallet_sales.index', compact('sales', 'sort', 'direction'));
    }

    public function create()
    {
        $ukuranList = \App\Models\PalletPrice::select('ukuran', 'harga_basah', 'harga_kering')->get();
        return view('pallet_sales.create', compact('ukuranList'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'tanggal' => 'required|date',
            'customer' => 'required|string|max:255',
            // Validasi minimal 1 item palet diisi
            'basah' => 'array',
            'kering' => 'array',
        ]);

        $basahFilled = collect($request->basah)->filter(function($item) {
            return intval($item['pcs'] ?? 0) > 0;
        })->count();

        $keringFilled = collect($request->kering)->filter(function($item) {
            return intval($item['pcs'] ?? 0) > 0;
        })->count();

        if (($basahFilled + $keringFilled) < 1) {
            return redirect()->back()
                ->withInput()
                ->with('Gagal', 'Minimal 1 item palet harus diisi jumlah PCS-nya!');
        }

        try {
            DB::transaction(function () use ($request) {
                $sale = PalletSale::create([
                    'kode_transaksi' => 'PJ-PLT-' . date('YmdHis'),
                    'tanggal' => $request->tanggal,
                    'customer' => $request->customer,
                    'keterangan' => $request->keterangan,
                ]);

                // Proses palet basah (FIFO)
                foreach ($request->basah ?? [] as $item) {
                    if (intval($item['pcs']) > 0) {
                        $palletPrice = PalletPrice::where('ukuran', $item['ukuran'])->first();
                        $pallet_code = $palletPrice ? $palletPrice->pallet_code : null;
                        $pcsNeeded = intval($item['pcs']);
                        $totalVolume = floatval($item['volume']);
                        $harga = floatval($item['harga_satuan']);
                        $subtotal = str_replace('.', '', $item['subtotal']);
                        $subtotal = floatval($subtotal);

                        PalletSaleItem::create([
                            'pallet_sale_id' => $sale->id,
                            'pallet_code' => $pallet_code,
                            'ukuran' => $item['ukuran'],
                            'status' => 'basah',
                            'pcs' => $pcsNeeded,
                            'volume' => $totalVolume,
                            'harga_satuan' => $harga, // harga basah
                            'subtotal' => $subtotal,
                        ]);

                        // FIFO pengurangan stok hanya berdasarkan PCS
                        $stocks = PalletStock::where('pallet_code', $pallet_code)
                            ->where('ukuran', $item['ukuran'])
                            ->where('status', 'basah')
                            ->where('pcs', '>', 0)
                            ->orderBy('tanggal_input')
                            ->get();

                        $totalPcsAvailable = $stocks->sum('pcs');

                        if ($totalPcsAvailable < $pcsNeeded) {
                            throw new \Exception('Stok tidak cukup untuk ukuran ' . $item['ukuran'] . ' (basah)');
                        }

                        $pcsToReduce = $pcsNeeded;

                        foreach ($stocks as $stock) {
                            if ($pcsToReduce <= 0) break;

                            $takePcs = min($stock->pcs, $pcsToReduce);

                            $stock->pcs -= $takePcs;
                            $stock->pcs = max(0, $stock->pcs);
                            $stock->save();

                            if (round($stock->pcs, 6) <= 0) {
                                $stock->delete();
                            }

                            $pcsToReduce -= $takePcs;
                        }
                    }
                }

                // Proses palet kering (FIFO)
                foreach ($request->kering ?? [] as $item) {
                    if (intval($item['pcs']) > 0) {
                        $palletPrice = PalletPrice::where('ukuran', $item['ukuran'])->first();
                        $pallet_code = $palletPrice ? $palletPrice->pallet_code : null;
                        $pcsNeeded = intval($item['pcs']);
                        $totalVolume = floatval($item['volume']);
                        $harga = floatval($item['harga_satuan']);
                        $subtotal = str_replace('.', '', $item['subtotal']);
                        $subtotal = floatval($subtotal);

                        PalletSaleItem::create([
                            'pallet_sale_id' => $sale->id,
                            'pallet_code' => $pallet_code,
                            'ukuran' => $item['ukuran'],
                            'status' => 'kering',
                            'pcs' => $pcsNeeded,
                            'volume' => $totalVolume,
                            'harga_satuan' => $harga, // harga kering
                            'subtotal' => $subtotal,
                        ]);

                        // FIFO pengurangan stok hanya berdasarkan PCS
                        $stocks = PalletStock::where('pallet_code', $pallet_code)
                            ->where('ukuran', $item['ukuran'])
                            ->where('status', 'kering') // <-- Ubah dari 'penjemuran' ke 'kering'
                            ->where('pcs', '>', 0)
                            ->orderBy('tanggal_input')
                            ->get();

                        $totalPcsAvailable = $stocks->sum('pcs');

                        if ($totalPcsAvailable < $pcsNeeded) {
                            throw new \Exception('Stok tidak cukup untuk ukuran ' . $item['ukuran'] . ' (kering)');
                        }

                        $pcsToReduce = $pcsNeeded;

                        foreach ($stocks as $stock) {
                            if ($pcsToReduce <= 0) break;

                            $takePcs = min($stock->pcs, $pcsToReduce);

                            $stock->pcs -= $takePcs;
                            $stock->pcs = max(0, $stock->pcs);
                            $stock->save();

                            if (round($stock->pcs, 6) <= 0) {
                                $stock->delete();
                            }

                            $pcsToReduce -= $takePcs;
                        }
                    }
                }
            });

            // Jika request AJAX, kirim response JSON sukses
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi penjualan palet berhasil disimpan!',
                    'redirect_url' => route('pallet-sales.index')
                ]);
            }

            // Jika bukan AJAX, redirect seperti biasa
            return redirect()->route('pallet-sales.index')->with('success', 'Transaksi penjualan palet berhasil disimpan!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function exportNota($id)
    {
        $sale = \App\Models\PalletSale::with('items')->findOrFail($id);

        // Ambil semua ukuran dari tabel harga
        $allUkuran = \App\Models\PalletPrice::pluck('ukuran')->unique();

        // Siapkan data basah dan penjemuran, pastikan semua ukuran ada
        $basah = collect();
        $kering = collect();

        foreach ($allUkuran as $ukuran) {
            // Cari item basah & penjemuran untuk ukuran ini
            $itemBasah = $sale->items->where('status', 'basah')->where('ukuran', $ukuran)->first();
            $itemKering = $sale->items->where('status', 'kering')->where('ukuran', $ukuran)->first();

            $basah->push((object)[
                'ukuran'   => $ukuran,
                'pcs'      => $itemBasah ? $itemBasah->pcs : 0,
                'volume'   => $itemBasah ? $itemBasah->volume : 0,
                'subtotal' => $itemBasah ? $itemBasah->subtotal : 0,
            ]);
            $kering->push((object)[
                'ukuran'   => $ukuran,
                'pcs'      => $itemKering ? $itemKering->pcs : 0,
                'volume'   => $itemKering ? $itemKering->volume : 0,
                'subtotal' => $itemKering ? $itemKering->subtotal : 0,
            ]);
        }

        $total_volume = $sale->items->sum('volume');
        $grand_total = $sale->items->sum('subtotal');

        $total_volume_basah = $basah->sum('volume');
        $grand_total_basah = $basah->sum('subtotal');

        $total_volume_kering = $kering->sum('volume');
        $grand_total_kering = $kering->sum('subtotal');

        $pdf = Pdf::loadView('exports.nota_pallet', compact(
            'sale',
            'total_volume',
            'grand_total',
            'basah',
            'kering',
            'total_volume_basah',
            'grand_total_basah',
            'total_volume_kering',
            'grand_total_kering'
        ))->setPaper('a5', 'landscape');

        return $pdf->download('nota-pallet-'.$sale->kode_transaksi.'.pdf');
    }

    public function laporanMingguan(Request $request)
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $sales = \App\Models\PalletSale::with('items')
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'asc')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.laporan_mingguan_pallet', compact('sales', 'start', 'end'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penjualan-pallet-mingguan.pdf');
    }

}