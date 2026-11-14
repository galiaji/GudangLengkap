<?php

namespace App\Http\Controllers;

use App\Models\WoodTransaction;
use App\Models\WoodTransactionDetail;
use App\Models\WoodStock;
use App\Models\WoodType;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class WoodTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = WoodTransaction::query();

        if ($request->filled('jenis_transaksi')) {
            $query->where('jenis_transaksi', $request->jenis_transaksi);
        }
        if ($request->filled('q')) {
            $query->where('atas_nama', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_transaksi', $request->tanggal);
        }

        $transaksi = $query->orderByDesc('tanggal_transaksi')->get();

        return view('wood_transactions.index', compact('transaksi'));
    }

    // Halaman form pembelian
    // public function buy()
    // {
    //     $woodTypes = ['Mahoni', 'Keras', 'Sengon'];

    //     // $stocks = WoodStock::whereIn('wood_type', $woodTypes)->get();
    //     $stocks = WoodStock::with('price')->get();
    //     // Buat array asosiatif: $grades[grade] = [diameter1, diameter2, ...]
    //     $grades = [];
    //     foreach ($stocks as $stock) {
    //         $grades[$stock->grade][] = $stock->diameter;
    //     }
    //     // Hilangkan duplikat diameter
    //     foreach ($grades as $grade => $diameters) {
    //         $grades[$grade] = array_values(array_unique($diameters));
    //         sort($grades[$grade]);
    //     }

    //     // Allowed length per grade
    //     $allowedLengths = [];
    //     foreach ($stocks as $stock) {
    //         $allowedLengths[$stock->grade][] = $stock->panjang;
    //     }
    //     foreach ($allowedLengths as $grade => $panjangs) {
    //         $allowedLengths[$grade] = array_values(array_unique($panjangs));
    //         sort($allowedLengths[$grade]);
    //     }

    //     // Ambil volume unik per diameter & panjang (tanpa grade)
    //     $stocks = DB::table('wood_stocks')->get();
    //     $volumes = [];
    //     foreach ($stocks as $row) {
    //         if (!isset($volumes[$row->diameter][$row->panjang])) {
    //             $volumes[$row->diameter][$row->panjang] = $row->volume ?? $row->volume_per_batang;
    //         }
    //     }

    //     $jenisKayu = request()->wood_type ?? null;

    //     // $hargaPerM3 = (new WoodPriceController)->getHargaPerM3();
    //     $hargaPerM3 = [];

    //     foreach ($stocks as $stock) {
    //         $hargaPerM3[$stock->wood_type_id][$stock->grade][$stock->panjang] = [
    //             'beli' => $stock->price->harga_beli_per_m3 ?? 0,
    //             // 'jual' => $stock->price->harga_jual_per_m3 ?? 0,
    //         ];
    //     }
    //     return view('wood_transactions.buy', compact(
    //         'jenisKayu', 'woodTypes', 'grades', 'allowedLengths', 'volumes', 'hargaPerM3'
    //     ));
    // }
public function buy()
{
    $woodTypes = WoodType::visible()->orderBy('name')->get();

    $stocks = WoodStock::with([
        'price',
        'woodType'
    ])->get();

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

    // Ambil volume unik per diameter & panjang
    $volumes = [];
    foreach ($stocks as $stock) {
        if (!isset($volumes[$stock->diameter][$stock->panjang])) {
            $volumes[$stock->diameter][$stock->panjang] = $stock->volume ?? $stock->volume_per_batang;
        }
    }
    // Kombinasi yang terdaftar per jenis kayu:
    // $availableCombos[wood_type_id][grade][diameter] = [panjang, ...]
    // Dipakai form untuk menampilkan hanya diameter/panjang milik jenis kayu terpilih.
    $availableCombos = [];
    foreach ($stocks as $stock) {
        $availableCombos[$stock->wood_type_id][$stock->grade][$stock->diameter][] = (int) $stock->panjang;
    }

    $jenisKayu = request()->wood_type_id ?? null;
    // Buat array harga per m³ dari relasi
    $hargaPerM3 = [];
    foreach ($stocks as $stock) {
        if ($stock->price) {
            $hargaPerM3[$stock->wood_type_id][$stock->grade][$stock->panjang] = [
                'beli' => $stock->price->harga_beli_per_m3 ?? 0,
                'jual' => $stock->price->harga_jual_per_m3 ?? 0,
            ];
        }
    }
//     dd(
//     $grades['Super 2'] ?? [],
//     $volumes[31] ?? 'tidak ada',
//     $volumes[40] ?? 'tidak ada'
// );
// dd(
//     $grades['Super 2'],
//     $allowedLengths['Super 2']
// );
    return view('wood_transactions.buy', compact(
        'jenisKayu', 'woodTypes', 'grades', 'allowedLengths', 'volumes', 'hargaPerM3', 'stocks', 'availableCombos'
    ));
}
public function storeBuy(Request $request)
{
    $request->validate([
        'atas_nama'        => 'required|string',
        'wood_type_id' => 'required|exists:wood_types,id,is_hidden,0',
        'total_volume'     => 'nullable|numeric',
        'total_pembayaran' => 'nullable|numeric',
        'details'          => 'required|array|min:1',
        'details.*.grade'  => 'required|string',
        'details.*.diameter' => 'required|numeric',
        'details.*.panjang'  => 'required|numeric',
        'details.*.pcs'      => 'required|integer|min:1',
        'details.*.volume'   => 'required|numeric|min:0',
        'details.*.harga'    => 'required|numeric|min:0',
    ], [
        'atas_nama.required'    => 'Kolom Atas Nama wajib diisi.',
        'wood_type_id.required' => 'Jenis Kayu wajib dipilih.',
        'wood_type_id.exists'   => 'Jenis Kayu tidak valid atau sedang disembunyikan.',
    ]);

    $woodType = WoodType::findOrFail($request->wood_type_id);

    DB::beginTransaction();
    try {
        // 1. Hanya kombinasi yang sudah terdaftar yang boleh dibeli.
        //    Kombinasi baru harus ditambahkan dulu lewat "Tambah Kombinasi Stok".
        $errors = [];
        $stocks = [];
        $stockById = [];
        foreach ($request->details as $i => $detail) {
            $stock = WoodStock::where([
                'wood_type_id' => $woodType->id,
                'grade'        => $detail['grade'],
                'diameter'     => $detail['diameter'],
                'panjang'      => $detail['panjang'],
            ])->lockForUpdate()->first();

            if (!$stock) {
                $errors[] = "Kombinasi {$woodType->name} grade {$detail['grade']}, diameter {$detail['diameter']}, panjang {$detail['panjang']} belum terdaftar. Tambahkan dulu lewat menu Tambah Kombinasi Stok.";
            } else {
                // Baris dengan kombinasi sama memakai objek stok yang sama agar jumlahnya terakumulasi
                $stock = $stockById[$stock->id] ??= $stock;
            }
            $stocks[$i] = $stock;
        }

        if (!empty($errors)) {
            DB::rollBack();
            return response()->json([
                'success'  => false,
                'messages' => $errors,
            ], 422);
        }

        // Volume & harga (per m³) dihitung ulang dari data stok, bukan dari browser
        $hitung = [];
        foreach ($request->details as $i => $detail) {
            $hitung[$i] = $stocks[$i]->hitungBaris((int) $detail['pcs'], 'harga_beli_per_m3');
        }

        // 2. Simpan transaksi utama
        $transaksi = WoodTransaction::create([
            'jenis_transaksi'   => 'Pembelian',
            'atas_nama'         => $request->atas_nama,
            'total_volume'      => collect($hitung)->sum(0), // dihitung dari rincian
            'total_pembayaran'  => collect($hitung)->sum(1), // dihitung dari rincian
            'tanggal_transaksi' => now(),
        ]);

        // 3. Tambah stok & simpan detail
        foreach ($request->details as $i => $detail) {
            $stock = $stocks[$i];
            [$volume, $harga] = $hitung[$i];

            // Update stok
            $stock->quantity     = ($stock->quantity ?? 0) + $detail['pcs'];
            $stock->total_volume = $stock->total_volume + $volume;
            $stock->save();

            // Simpan detail transaksi dengan semua kolom wajib
            WoodTransactionDetail::create([
                'wood_transaction_id' => $transaksi->id,
                'wood_stock_id'       => $stock->id,
                'wood_type_id'        => $request->wood_type_id,
                'grade'               => $detail['grade'],
                'diameter'            => $detail['diameter'],
                'panjang'             => $detail['panjang'],
                'pcs'                 => $detail['pcs'],
                'volume'              => $volume,
                'harga'               => $harga,
            ]);
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'redirect_url' => route('transaksi.show', Hashids::encode($transaksi->id))
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Penyimpanan gagal. ' . $e->getMessage()
        ], 422);
    }
}



    // Halaman form penjualan
    // public function sell()
    // {
    //     $woodTypes = ['Mahoni', 'Keras', 'Sengon'];

    //     $stocks = WoodStock::whereIn('wood_type', $woodTypes)->get();

    //     // Mapping grade ke rentang
    //     $gradeRanges = [
    //         'Rijek 1' => 'Rijek 10 - 15',
    //         'Rijek 2' => 'Rijek 16 - 30',
    //         'Storty 1' => 'Storty 13 - 15',
    //         'Storty 2' => 'Storty 16 - 18',
    //         'Super 1' => 'Super 19 - 25',
    //         'Super 2' => 'Super 25 Up',
    //     ];

    //     $grades = [];
    //     foreach ($stocks as $stock) {
    //         $grades[$stock->grade][] = $stock->diameter;
    //     }
    //     foreach ($grades as $grade => $diameters) {
    //         $grades[$grade] = array_values(array_unique($diameters));
    //         sort($grades[$grade]);
    //     }

    //     // Allowed length per grade
    //     $allowedLengths = [];
    //     foreach ($stocks as $stock) {
    //         $allowedLengths[$stock->grade][] = $stock->panjang;
    //     }
    //     foreach ($allowedLengths as $grade => $panjangs) {
    //         $allowedLengths[$grade] = array_values(array_unique($panjangs));
    //         sort($allowedLengths[$grade]);
    //     }

    //     // Ambil volume unik per diameter & panjang (tanpa grade)
    //     $stocks = DB::table('wood_stocks')->get();
    //     $volumes = [];
    //     foreach ($stocks as $row) {
    //         if (!isset($volumes[$row->diameter][$row->panjang])) {
    //             $volumes[$row->diameter][$row->panjang] = $row->volume ?? $row->volume_per_batang;
    //         }
    //     }

    //     $jenisKayu = request()->wood_type ?? null;

    //     // Ambil semua harga dari wood_prices
    //     $hargaPerM3 = (new WoodPriceController)->getHargaPerM3();

    //     return view('wood_transactions.sell', [
    //         'woodTypes' => $woodTypes,
    //         'grades' => $grades,
    //         'allowedLengths' => $allowedLengths,
    //         'hargaPerM3' => $hargaPerM3,
    //         'jenisKayu' => $jenisKayu,
    //         'volumes' => $volumes,
    //         'gradeRanges' => $gradeRanges,
    //     ]);
    // }

public function sell()
{
    $woodTypes = WoodType::visible()->orderBy('name')->get();

    $stocks = WoodStock::whereNotNull('wood_type_id')->get();

    // Mapping grade ke rentang
    $gradeRanges = [
        'Rijek 1' => 'Rijek 10 - 15',
        'Rijek 2' => 'Rijek 16 - 30',
        'Storty 1' => 'Storty 13 - 15',
        'Storty 2' => 'Storty 16 - 18',
        'Super 1' => 'Super 19 - 25',
        'Super 2' => 'Super 25 Up',
    ];

    // Grades
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

    // Ambil volume unik per diameter & panjang
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
        $stockData[$stock->wood_type_id][$stock->grade][$stock->diameter][$stock->panjang] = $stock->quantity ?? 0;
    }

    $jenisKayu = request()->wood_type_id ?? null;

    // Ambil semua harga dari wood_prices
    $hargaPerM3 = (new WoodPriceController)->getHargaPerM3();

    return view('wood_transactions.sell', [
        'woodTypes' => $woodTypes,
        'grades' => $grades,
        'allowedLengths' => $allowedLengths,
        'hargaPerM3' => $hargaPerM3,
        'jenisKayu' => $jenisKayu,
        'volumes' => $volumes,
        'gradeRanges' => $gradeRanges,
        'stockData' => $stockData,
    ]);
}


    // Simpan transaksi penjualan
    // public function storeSell(Request $request)
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
    //         ], [
    //             'atas_nama.required' => 'Kolom atas nama wajib diisi.',
    //             'details.*.pcs.required' => 'Setidaknya 1 data log ter isi',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $e->errors(),
    //             'message' => 'Validasi gagal. Silakan periksa input Anda.'
    //         ], 422);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Simpan transaksi utama
    //         $transaksi = WoodTransaction::create([
    //             'jenis_transaksi' => 'Penjualan',
    //             'atas_nama' => $request->atas_nama,
    //             'wood_type' => $request->wood_type,
    //             'total_volume' => $request->total_volume,
    //             'total_pembayaran' => $request->total_pembayaran,
    //             'tanggal_transaksi' => now(),
    //         ]);

    //         $errors = [];
    //         foreach ($request->details as $detail) {
    //             $stock = WoodStock::where([
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
    //             WoodTransactionDetail::create([
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
    //             $stock = WoodStock::where([
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
    //             'redirect_url' => route('transaksi.show', \Vinkla\Hashids\Facades\Hashids::encode($transaksi->id))
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function storeSell(Request $request)
    {
            $request->validate([
                'atas_nama'        => 'required|string',
                'wood_type_id'     => 'required|exists:wood_types,id,is_hidden,0',
                'total_volume'     => 'nullable|numeric',
                'total_pembayaran' => 'nullable|numeric',
                'details'          => 'required|array|min:1',
                'details.*.grade'  => 'required|string',
                'details.*.diameter' => 'required|numeric',
                'details.*.panjang'  => 'required|numeric',
                'details.*.pcs'      => 'required|integer|min:1',
                'details.*.volume'   => 'required|numeric|min:0',
                'details.*.harga'    => 'required|numeric|min:0',
            ], [
                'atas_nama.required'    => 'Kolom atas nama wajib diisi.',
                'wood_type_id.required' => 'Jenis Kayu wajib dipilih.',
                'wood_type_id.exists'   => 'Jenis Kayu tidak valid atau sedang disembunyikan.',
                'details.*.pcs.required' => 'Setidaknya 1 data log terisi.',
            ]);

            DB::beginTransaction();
            try {
                $errors = [];
                $stocks = [];
                $stockById = [];
                $needed = [];

                // Validasi stok sebelum transaksi
                foreach ($request->details as $i => $detail) {
                    $stock = WoodStock::where([
                        'wood_type_id' => $request->wood_type_id,
                        'grade'        => $detail['grade'],
                        'diameter'     => $detail['diameter'],
                        'panjang'      => $detail['panjang'],
                    ])->lockForUpdate()->first();

                    if ($stock) {
                        // Baris dengan kombinasi sama memakai objek stok yang sama, dan pcs-nya dijumlahkan
                        $stock = $stockById[$stock->id] ??= $stock;
                        $needed[$stock->id] = ($needed[$stock->id] ?? 0) + $detail['pcs'];
                    }

                    if (!$stock) {
                        $errors[] = "Stok kayu tidak ditemukan untuk grade {$detail['grade']}, diameter {$detail['diameter']}, panjang {$detail['panjang']}.";
                    } elseif (($stock->quantity ?? 0) < $needed[$stock->id]) {
                        $errors[] = "Stok kayu tidak mencukupi untuk grade {$detail['grade']}, diameter {$detail['diameter']}, panjang {$detail['panjang']}. Maksimal " . ($stock->quantity ?? 0) . " pcs.";
                    }
                    $stocks[$i] = $stock;
                }

                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json([
                        'success'  => false,
                        'messages' => $errors,
                    ], 422);
                }

                // Volume & harga (per m³) dihitung ulang dari data stok, bukan dari browser
                $hitung = [];
                foreach ($request->details as $i => $detail) {
                    $hitung[$i] = $stocks[$i]->hitungBaris((int) $detail['pcs'], 'harga_jual_per_m3');
                }

                // Simpan transaksi utama
                $transaksi = WoodTransaction::create([
                    'jenis_transaksi'   => 'Penjualan',
                    'atas_nama'         => $request->atas_nama,
                    'total_volume'      => collect($hitung)->sum(0), // dihitung dari rincian
                    'total_pembayaran'  => collect($hitung)->sum(1), // dihitung dari rincian
                    'tanggal_transaksi' => now(),
                ]);

                // Simpan detail transaksi dan kurangi stok
                foreach ($request->details as $i => $detail) {
                    $stock = $stocks[$i];
                    [$volume, $harga] = $hitung[$i];

                    WoodTransactionDetail::create([
                        'wood_transaction_id' => $transaksi->id,
                        'wood_stock_id'       => $stock->id,
                        'wood_type_id'        => $request->wood_type_id,
                        'grade'               => $detail['grade'],
                        'diameter'            => $detail['diameter'],
                        'panjang'             => $detail['panjang'],
                        'pcs'                 => $detail['pcs'],
                        'volume'              => $volume,
                        'harga'               => $harga,
                    ]);

                    // Kurangi stok
                    $stock->quantity     = ($stock->quantity ?? 0) - $detail['pcs'];
                    $stock->total_volume = max(0, $stock->total_volume - $volume);
                    $stock->save();
                }

                DB::commit();
                return response()->json([
                    'success'      => true,
                    'redirect_url' => route('transaksi.show', \Vinkla\Hashids\Facades\Hashids::encode($transaksi->id)),
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
                return response()->json([
                    'success' => false,
                    'message' => 'Penyimpanan gagal. ' . $e->getMessage(),
                ], 500);
            }
    }

    // Halaman detail transaksi
    public function show($hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        $transaksi = WoodTransaction::with("details.woodType")->findOrFail($id);
        return view('wood_transactions.show', compact('transaksi'));
    }

    public function laporanHarian()
    {
        $today = now()->toDateString();

        $transaksi = WoodTransaction::with('details')
        ->whereDate(
            'tanggal_transaksi',
            $today
        )
        ->get();

        $pembelian = $transaksi->where('jenis_transaksi', 'Pembelian');

        $penjualan = $transaksi->where('jenis_transaksi', 'Penjualan');

        return $this->unduhLaporan([
            'judul' => 'Laporan Harian',
            'periode' => \Carbon\Carbon::parse($today)
                ->translatedFormat('d F Y'),

            'pembelian' => $pembelian,
            'penjualan' => $penjualan,

            'totalVolumePembelian' =>
                $pembelian->sum('total_volume'),

            'totalPembayaranPembelian' =>
                $pembelian->sum('total_pembayaran'),

            'totalVolumePenjualan' =>
                $penjualan->sum('total_volume'),

            'totalPembayaranPenjualan' =>
                $penjualan->sum('total_pembayaran'),
        ]);
    }

    public function laporanBulanan(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        $bulan = $request->input(
            'bulan',
            now()->format('m')
        );

        $tahun = $request->input(
            'tahun',
            now()->format('Y')
        );

        $transaksi = WoodTransaction::with('details')
        ->whereYear(
            'tanggal_transaksi',
            $tahun
        )
        ->whereMonth(
            'tanggal_transaksi',
            $bulan
        )
        ->get();

        $pembelian = $transaksi->where(
            'jenis_transaksi',
            'Pembelian'
        );

        $penjualan = $transaksi->where(
            'jenis_transaksi',
            'Penjualan'
        );

        return $this->unduhLaporan([
            'judul' => 'Laporan Bulanan',
            'periode' => \Carbon\Carbon::create($tahun, $bulan)
                ->translatedFormat('F Y'),

            'pembelian' => $pembelian,
            'penjualan' => $penjualan,

            'totalVolumePembelian' =>
                $pembelian->sum('total_volume'),

            'totalPembayaranPembelian' =>
                $pembelian->sum('total_pembayaran'),

            'totalVolumePenjualan' =>
                $penjualan->sum('total_volume'),

            'totalPembayaranPenjualan' =>
                $penjualan->sum('total_pembayaran'),
        ]);
    }

    public function laporanTahunan(Request $request)
    {
        $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        $tahun = $request->input(
            'tahun',
            now()->format('Y')
        );

        $transaksi = WoodTransaction::with('details')
        ->whereYear(
            'tanggal_transaksi',
            $tahun
        )
        ->get();

        $pembelian = $transaksi->where(
            'jenis_transaksi',
            'Pembelian'
        );

        $penjualan = $transaksi->where(
            'jenis_transaksi',
            'Penjualan'
        );

        return $this->unduhLaporan([
            'judul' => 'Laporan Tahunan',
            'periode' => $tahun,

            'pembelian' => $pembelian,
            'penjualan' => $penjualan,

            'totalVolumePembelian' =>
                $pembelian->sum('total_volume'),

            'totalPembayaranPembelian' =>
                $pembelian->sum('total_pembayaran'),

            'totalVolumePenjualan' =>
                $penjualan->sum('total_volume'),

            'totalPembayaranPenjualan' =>
                $penjualan->sum('total_pembayaran'),
        ]);
    }

    // Laporan transaksi langsung diunduh sebagai file PDF
    private function unduhLaporan(array $data)
    {
        $namaFile = Str::slug($data['judul'] . ' ' . $data['periode']) . '.pdf';

        return Pdf::loadView('wood_transactions.laporan', $data + ['pdf' => true])
            ->setPaper('a4', 'portrait')
            ->download($namaFile);
    }
}
