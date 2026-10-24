<?php

namespace App\Http\Controllers;

use App\Models\WoodStock;
use App\Models\WoodType;
use App\Models\WoodTransaction;
use App\Models\WoodTransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function detail($woodTypeId)
    {
        // Ambil semua stok berdasarkan nama wood_type (bukan id)
        $woodType = WoodType::findOrFail($woodTypeId);

    $stocks = WoodStock::with('woodType')
        ->where('wood_type_id', $woodTypeId)
        ->get();
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
        $woodTypes = WoodType::visible()->orderBy('name')->get();

        $stocks = WoodStock::with('woodType')->get();
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
            $stockData[$stock->wood_type_id][$stock->grade][$stock->diameter][$stock->panjang] = $stock->quantity ?? 0;
        }

        $jenisKayu = request()->wood_type_id ?? null;

        // Harga per m3, dikelompokkan per wood_type_id
        $hargaPerM3 = (new WoodPriceController)->getHargaPerM3();

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
public function storeSaw(Request $request)
{
    $request->validate([
        'atas_nama'        => 'required|string',
        'wood_type_id' => 'required|exists:wood_types,id,is_hidden,0',
        'total_volume'     => 'nullable|numeric',
        'total_pembayaran' => 'nullable|numeric',
        'details'          => 'required|array|min:1',
        'details.*.grade'  => 'required|string',
        'details.*.diameter' => 'required|numeric|min:0',
        'details.*.panjang'  => 'required|numeric|min:0',
        'details.*.pcs'      => 'required|integer|min:1',
        'details.*.volume'   => 'required|numeric|min:0',
        'details.*.harga'    => 'required|numeric|min:0',
        'jenis_transaksi'    => 'required|in:gergajian',
    ], [
        'atas_nama.required'    => 'Kolom Atas Nama wajib diisi.',
        'wood_type_id.required' => 'Jenis Kayu wajib dipilih.',
        'wood_type_id.exists'   => 'Jenis Kayu tidak valid atau sedang disembunyikan.',
        'details.required'      => 'Minimal 1 log kayu harus dimasukkan.',
    ]);

    $woodType = WoodType::findOrFail($request->wood_type_id);

    DB::beginTransaction();
    try {
        // 1. Cek semua stok dulu sebelum menyimpan apa pun
        $errors = [];
        $stocks = [];
        $stockById = [];
        $needed = [];
        foreach ($request->details as $i => $detail) {
            $stock = WoodStock::where([
                'wood_type_id' => $woodType->id,
                'grade'        => $detail['grade'],
                'diameter'     => $detail['diameter'],
                'panjang'      => $detail['panjang'],
            ])->lockForUpdate()->first();

            if ($stock) {
                // Baris dengan kombinasi sama memakai objek stok yang sama, dan pcs-nya dijumlahkan
                $stock = $stockById[$stock->id] ??= $stock;
                $needed[$stock->id] = ($needed[$stock->id] ?? 0) + $detail['pcs'];
            }

            $label = "jenis {$woodType->name}, grade {$detail['grade']}, diameter {$detail['diameter']}, panjang {$detail['panjang']}";
            if (!$stock) {
                $errors[] = "Stok kayu tidak ditemukan untuk {$label}.";
            } elseif (($stock->quantity ?? 0) < $needed[$stock->id]) {
                $errors[] = "Stok kayu tidak mencukupi untuk {$label}. Maksimal " . ($stock->quantity ?? 0) . " pcs.";
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

        // 2. Simpan transaksi utama
        $transaksi = WoodTransaction::create([
            'jenis_transaksi'   => 'gergajian',
            'atas_nama'         => $request->atas_nama,
            'total_volume'      => collect($request->details)->sum('volume'), // dihitung dari rincian
            'total_pembayaran'  => collect($request->details)->sum('harga'), // dihitung dari rincian
            'tanggal_transaksi' => now(),
        ]);

        // 3. Simpan detail & kurangi stok
        foreach ($request->details as $i => $detail) {
            $stock = $stocks[$i];

            WoodTransactionDetail::create([
                'wood_transaction_id' => $transaksi->id,
                'wood_stock_id'       => $stock->id,
                'wood_type_id'        => $woodType->id,
                'grade'               => $detail['grade'],
                'diameter'            => $detail['diameter'],
                'panjang'             => $detail['panjang'],
                'pcs'                 => $detail['pcs'],
                'volume'              => $detail['volume'],
                'harga'               => $detail['harga'],
            ]);

            $stock->quantity     = ($stock->quantity ?? 0) - $detail['pcs'];
            $stock->total_volume = max(0, $stock->total_volume - $detail['volume']);
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
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        ], 500);
    }
}

public function storeRange(Request $request)
{
    $data = $request->validate([
        'wood_type_id' => 'required|exists:wood_types,id,is_hidden,0',
        'grade'        => 'required|string|max:50',
        'diameter'     => 'required|integer|min:1',
        'panjang'      => 'required|integer|in:100,130,200,260',
    ], [
        'wood_type_id.required' => 'Jenis kayu wajib dipilih.',
        'wood_type_id.exists'   => 'Jenis kayu tidak valid atau sedang disembunyikan.',
        'grade.required'        => 'Grade wajib dipilih.',
        'diameter.required'     => 'Diameter wajib diisi.',
        'panjang.required'      => 'Panjang wajib dipilih.',
    ]);

    $woodType = WoodType::findOrFail($data['wood_type_id']);

    // Tolak kalau kombinasi sudah ada
    $exists = WoodStock::where([
        'wood_type_id' => $woodType->id,
        'grade'        => $data['grade'],
        'diameter'     => $data['diameter'],
        'panjang'      => $data['panjang'],
    ])->exists();

    if ($exists) {
        return back()->withInput()->withErrors([
            'diameter' => "Kombinasi {$woodType->name} {$data['grade']} diameter {$data['diameter']} panjang {$data['panjang']} sudah ada.",
        ]);
    }

    // Volume per batang (m3): pakai yang sudah ada untuk diameter & panjang ini,
    // kalau belum ada hitung rumus silinder: pi/4 x (d/100)^2 x (p/100)
    $volume = WoodStock::where('diameter', $data['diameter'])
        ->where('panjang', $data['panjang'])
        ->value('volume');

    if ($volume === null) {
        $volume = round(M_PI / 4 * pow($data['diameter'] / 100, 2) * ($data['panjang'] / 100), 3);
    }

    DB::transaction(function () use ($data, $woodType, $volume) {
        // Tabel wood_prices masih memakai nama jenis kayu
        $price = \App\Models\WoodPrice::firstOrCreate(
            [
                'wood_type' => $woodType->name,
                'grade'     => $data['grade'],
                'panjang'   => $data['panjang'],
            ],
            [
                'harga_beli_per_m3' => 0,
                'harga_jual_per_m3' => 0,
            ]
        );

        $stock = WoodStock::create([
            'wood_type_id' => $woodType->id,
            'grade'        => $data['grade'],
            'diameter'     => $data['diameter'],
            'panjang'      => $data['panjang'],
            'quantity'     => 0,
            'total_volume' => 0,
            'volume'       => $volume,
        ]);

        // wood_price_id tidak ada di $fillable, jadi diisi langsung
        $stock->wood_price_id = $price->id;
        $stock->save();
    });

    return redirect()
        ->route('stock.detail', $woodType->id)
        ->with('success', "Kombinasi stok {$woodType->name} {$data['grade']} D{$data['diameter']} P{$data['panjang']} berhasil ditambahkan.");
}

public function createRange()
    {
        $woodTypes = WoodType::visible()->orderBy('name')->get();

        $grades = [
            'Rijek 1',
            'Rijek 2',
            'Storty 1',
            'Storty 2',
            'Super 1',
            'Super 2',
        ];

        return view('WoodStock.create_range', compact(
            'woodTypes',
            'grades'
        ));
}

}
