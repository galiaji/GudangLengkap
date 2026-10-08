<?php

namespace App\Http\Controllers;

use App\Models\WoodStock;
use App\Models\WoodTransaction;
use App\Models\WoodTransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function detail($woodType)
    {
        // Ambil semua stok berdasarkan nama wood_type (bukan id)
        $stocks = WoodStock::where('wood_type', $woodType)->get();

        // Ambil semua grade unik dari stok (sebagai string)
        $grades = $stocks->pluck('grade')->unique()->values();

        // Kelompokkan stok per grade, diameter, panjang
        $grouped = [];
        foreach ($grades as $grade) {
            $grouped[$grade] = $stocks->where('grade', $grade)->groupBy('diameter');
        }

        $gradeRanges = [
            'Rijek 1' => 'Rijek 10 - 15',
            'Rijek 2' => 'Rijek 16 - 30',
            'Storty 1' => 'Storty 13 - 15',
            'Storty 2' => 'Storty 16 - 18',
            'Super 1' => 'Super 19 - 25',
            'Super 2' => 'Super 25 Up',
        ];

        // Kirim ke view
        return view('stock_detail', compact('woodType', 'grades', 'stocks', 'grouped', 'gradeRanges'));
    }


   
    public function showPotongGergajiForm()
    {
        $woodTypes = ['Mahoni', 'Keras', 'Sengon'];

        // Ambil stok dari model
        $stocks = \App\Models\WoodStock::whereIn('wood_type', $woodTypes)->get();

        // Mapping grade ke rentang
        $gradeRanges = [
            'Rijek 1' => 'Rijek 10 - 15',
            'Rijek 2' => 'Rijek 16 - 30',
            'Storty 1' => 'Storty 13 - 15',
            'Storty 2' => 'Storty 16 - 18',
            'Super 1' => 'Super 19 - 25',
            'Super 2' => 'Super 25 Up',
        ];

        // Buat array asosiatif: $grades[grade] = [diameter1, diameter2, ...]
        $grades = [];
        foreach ($stocks as $stock) {
            $grades[$stock->grade][] = $stock->diameter;
        }
        foreach ($grades as $grade => $diameters) {
            $grades[$grade] = array_values(array_unique($diameters));
            sort($grades[$grade]);
        }

        // Allowed length per grade
        $allowedLengths = [];
        foreach ($stocks as $stock) {
            $allowedLengths[$stock->grade][] = $stock->panjang;
        }
        foreach ($allowedLengths as $grade => $panjangs) {
            $allowedLengths[$grade] = array_values(array_unique($panjangs));
            sort($allowedLengths[$grade]);
        }

        // Ambil volume unik per diameter & panjang (tanpa grade)
        $stocksAll = DB::table('wood_stocks')->get();
        $volumes = [];
        foreach ($stocksAll as $row) {
            if (!isset($volumes[$row->diameter][$row->panjang])) {
                $volumes[$row->diameter][$row->panjang] = $row->volume ?? $row->volume_per_batang;
            }
        }

        // Data stok untuk validasi frontend
        $stockData = [];
        foreach ($stocks as $stock) {
            $stockData[$stock->grade][$stock->diameter][$stock->panjang] = $stock->pcs ?? 0;
        }

        $jenisKayu = request()->wood_type ?? null;

        // Ambil semua harga dari wood_prices
        $allPrices = DB::table('wood_prices')->get();
        $hargaPerM3 = [];
        foreach ($allPrices as $row) {
            $hargaPerM3[$row->wood_type][$row->grade][$row->panjang] = [
                'beli' => $row->harga_beli_per_m3,
                'jual' => $row->harga_jual_per_m3,
            ];
        }

        return view('WoodStock.potong', compact(
            'jenisKayu',
            'woodTypes',
            'grades',
            'allowedLengths',
            'volumes',
            'hargaPerM3',
            'gradeRanges',
            'stockData'
        ));
    }

    
    // public function storeSaw(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'atas_nama' => 'required|string',
    //             'wood_type' => 'required|string',
    //             'total_volume' => 'required|numeric',
    //             'total_pembayaran' => 'required|numeric',
    //             'details' => 'required|array|min:1',
    //             'details.*.grade' => 'required|string',
    //             'details.*.diameter' => 'required|numeric',
    //             'details.*.panjang' => 'required|numeric',
    //             'details.*.pcs' => 'required|integer',
    //             'details.*.volume' => 'required|numeric',
    //             'details.*.harga' => 'required|numeric',
    //             'jenis_transaksi' => 'required|in:gergajian',
    //         ], [
    //             'atas_nama.required' => 'Kolom atas nama wajib diisi.',
    //             'details' => 'Setidaknya 1 data log ter isi',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $e->errors(),
    //             'message' => 'Penyimpanan gagal. Silakan periksa lagi.'
    //         ], 422);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Simpan transaksi utama
    //         $transaksi = \App\Models\WoodTransaction::create([
    //             'jenis_transaksi' => 'gergajian',
    //             'atas_nama' => $request->atas_nama,
    //             'wood_type' => $request->wood_type,
    //             'total_volume' => $request->total_volume,
    //             'total_pembayaran' => $request->total_pembayaran,
    //             'tanggal_transaksi' => now(),
    //         ]);

    //         $errors = [];
    //         foreach ($request->details as $detail) {
    //             $stock = \App\Models\WoodStock::where([
    //                 'wood_type' => $request->wood_type,
    //                 'grade' => $detail['grade'],
    //                 'diameter' => $detail['diameter'],
    //                 'panjang' => $detail['panjang'],
    //             ])->first();

    //             if ($stock) {
    //                 if ($stock->quantity < $detail['pcs']) {
    //                     $errors[] = 'Stok kayu tidak mencukupi untuk grade ' . $detail['grade'] . ', diameter ' . $detail['diameter'] . ', panjang ' . $detail['panjang'] . '.';
    //                 }
    //             } else {
    //                 $errors[] = 'Stok kayu tidak ditemukan untuk grade ' . $detail['grade'] . ', diameter ' . $detail['diameter'] . ', panjang ' . $detail['panjang'] . '.';
    //             }
    //         }

    //         if (count($errors) > 0) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'messages' => $errors
    //             ], 422);
    //         }

    //         // Simpan detail & update stok
    //         foreach ($request->details as $detail) {
    //             \App\Models\WoodTransactionDetail::create([
    //                 'wood_transaction_id' => $transaksi->id,
    //                 'wood_type' => $request->wood_type,
    //                 'grade' => $detail['grade'],
    //                 'diameter' => $detail['diameter'],
    //                 'panjang' => $detail['panjang'],
    //                 'pcs' => $detail['pcs'],
    //                 'volume' => $detail['volume'],
    //                 'harga' => $detail['harga'],
    //             ]);

    //             // Kurangi stok dengan pengecekan
    //             $stock = \App\Models\WoodStock::where([
    //                 'wood_type' => $request->wood_type,
    //                 'grade' => $detail['grade'],
    //                 'diameter' => $detail['diameter'],
    //                 'panjang' => $detail['panjang'],
    //             ])->first();

    //             if ($stock) {
    //                 $stock->decrement('quantity', $detail['pcs']);
    //                 $stock->decrement('total_volume', $detail['volume']);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'redirect_url' => route('transaksi.index') 
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

public function storeSaw(Request $request)
{
    $request->validate([
        'atas_nama'        => 'required|string',
        'wood_type'        => 'required|string',
        'total_volume'     => 'required|numeric|min:0',
        'total_pembayaran' => 'required|numeric|min:0',
        'details'          => 'required|array|min:1',
        'details.*.grade'  => 'required|string',
        'details.*.diameter' => 'required|numeric|min:0',
        'details.*.panjang'  => 'required|numeric|min:0',
        'details.*.pcs'      => 'required|integer|min:1',
        'details.*.volume'   => 'required|numeric|min:0',
        'details.*.harga'    => 'required|numeric|min:0',
        'jenis_transaksi'    => 'required|in:gergajian',
    ]);

    DB::beginTransaction();
    try {
        $transaksi = WoodTransaction::create([
            'jenis_transaksi'   => 'gergajian',
            'atas_nama'         => $request->atas_nama,
            'wood_type'         => $request->wood_type,
            'total_volume'      => $request->total_volume,
            'total_pembayaran'  => $request->total_pembayaran,
            'tanggal_transaksi' => now(),
        ]);

        $errors = [];
        $stocksCache = [];
        
        foreach ($request->details as $detail) {
        $stock = WoodStock::where([
            'wood_type' => $request->wood_type,
            'grade'     => $detail['grade'],
            'diameter'  => $detail['diameter'],
            'panjang'   => $detail['panjang'],
        ])->first();

        if (!$stock) {
            $errors[] = "Stok kayu tidak ditemukan untuk jenis {$request->wood_type}, grade {$detail['grade']}, diameter {$detail['diameter']}, panjang {$detail['panjang']}.";
        } elseif ($stock->quantity < $detail['pcs']) { // ✅ gunakan quantity
            $errors[] = "Stok kayu tidak mencukupi untuk jenis {$request->wood_type}, grade {$detail['grade']}, diameter {$detail['diameter']}, panjang {$detail['panjang']}. Maksimal {$stock->quantity} pcs.";
        }

        // simpan ke cache supaya bisa dipakai di loop berikutnya
        $key = "{$request->wood_type}-{$detail['grade']}-{$detail['diameter']}-{$detail['panjang']}";
        $stocksCache[$key] = $stock;
    }

    if (!empty($errors)) {
        DB::rollBack();
        return response()->json([
            'success'  => false,
            'messages' => $errors,
        ], 422);
    }

    // ✅ Simpan detail & kurangi stok
    foreach ($request->details as $detail) {
        $key = "{$request->wood_type}-{$detail['grade']}-{$detail['diameter']}-{$detail['panjang']}";
        $stock = $stocksCache[$key];

        WoodTransactionDetail::create([
            'wood_transaction_id' => $transaksi->id,
            'wood_stock_id'       => $stock->id,
            'wood_type'           => $request->wood_type,
            'grade'               => $detail['grade'],
            'diameter'            => $detail['diameter'],
            'panjang'             => $detail['panjang'],
            'pcs'                 => $detail['pcs'],
            'volume'              => $detail['volume'],
            'harga'               => $detail['harga'],
        ]);

        $stock->decrement('quantity', $detail['pcs']);   // ✅ konsisten pakai quantity
        $stock->decrement('total_volume', $detail['volume']);
    }


        DB::commit();
        return response()->json([
            'success'      => true,
            'redirect_url' => route('transaksi.show', \Vinkla\Hashids\Facades\Hashids::encode($transaksi->id)),
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        ], 500);
    }
}



}
