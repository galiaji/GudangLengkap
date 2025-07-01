<?php

namespace App\Http\Controllers;

use App\Models\PalletSale;
use App\Models\PalletSaleItem;
use App\Models\PalletPrice;
use App\Models\PalletStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'tanggal' => 'required|date',
            'customer' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

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
                            'harga_satuan' => $harga,
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
                            'status' => 'penjemuran',
                            'pcs' => $pcsNeeded,
                            'volume' => $totalVolume,
                            'harga_satuan' => $harga,
                            'subtotal' => $subtotal,
                        ]);

                        // FIFO pengurangan stok hanya berdasarkan PCS
                        $stocks = PalletStock::where('pallet_code', $pallet_code)
                            ->where('ukuran', $item['ukuran'])
                            ->where('status', 'penjemuran')
                            ->where('pcs', '>', 0)
                            ->orderBy('tanggal_input')
                            ->get();

                        $totalPcsAvailable = $stocks->sum('pcs');

                        if ($totalPcsAvailable < $pcsNeeded) {
                            throw new \Exception('Stok tidak cukup untuk ukuran ' . $item['ukuran'] . ' (penjemuran)');
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
}