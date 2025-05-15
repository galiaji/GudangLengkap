<?php

namespace App\Http\Controllers;

use App\Models\WoodTransaction;
use App\Models\WoodTransactionDetail;
use App\Models\WoodStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WoodTransactionController extends Controller
{
    public function index()
    {
        $transaksi = WoodTransaction::with('details')->get();
        return view('wood_transactions.index', compact('transaksi'));
    }

    // Halaman form pembelian
    public function buy()
    {
        $woodTypes = ['Mahoni', 'Keras', 'Sengon'];

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
        // Hilangkan duplikat diameter
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
       $stocks = DB::table('wood_stocks')->get();

// Ambil volume unik per diameter & panjang (tanpa grade)
        $volumes = [];
        foreach ($stocks as $row) {
            // Jika kombinasi diameter-panjang belum pernah di-set, baru set
            if (!isset($volumes[$row->diameter][$row->panjang])) {
                $volumes[$row->diameter][$row->panjang] = $row->volume ?? $row->volume_per_batang;
            }
        }

        $jenisKayu = 'Mahoni';

        // Ambil harga per m3 dari tabel wood_prices
        $woodPrices = DB::table('wood_prices')->whereIn('wood_type', $woodTypes)->get();
        $hargaPerM3 = [];
        foreach ($woodPrices as $price) {
            $hargaPerM3[$price->grade][$price->panjang] = $price->price_per_m3;
        }

        return view('wood_transactions.buy', [
            'woodTypes' => $woodTypes,
            'grades' => $grades,
            'allowedLengths' => $allowedLengths, // sekarang per grade!
            'hargaPerM3' => $hargaPerM3,
            'jenisKayu' => $jenisKayu,
            'volumes' => $volumes,
            'gradeRanges' => $gradeRanges,
        ]);
    }

    // Simpan transaksi pembelian
    public function storeBuy(Request $request)
    {
        $request->validate([
            'atas_nama' => 'required|string',
            'total_volume' => 'required|numeric',
            'total_pembayaran' => 'required|numeric',
            'tanggal_transaksi' => 'required|date',
            'details' => 'required|array|min:1',
            'details.*.wood_type' => 'required|string',
            'details.*.grade' => 'required|string',
            'details.*.diameter' => 'required|numeric',
            'details.*.panjang' => 'required|numeric',
            'details.*.pcs' => 'required|integer',
            'details.*.volume' => 'required|numeric',
            'details.*.harga' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $transaksi = WoodTransaction::create([
                'jenis_transaksi' => 'Pembelian',
                'atas_nama' => $request->atas_nama,
                'total_volume' => $request->total_volume,
                'total_pembayaran' => $request->total_pembayaran,
                'tanggal_transaksi' => $request->tanggal_transaksi,
            ]);

            foreach ($request->details as $detail) {
                WoodTransactionDetail::create([
                    'wood_transaction_id' => $transaksi->id,
                    'wood_type' => $detail['wood_type'],
                    'grade' => $detail['grade'],
                    'diameter' => $detail['diameter'],
                    'panjang' => $detail['panjang'],
                    'pcs' => $detail['pcs'],
                    'volume' => $detail['volume'],
                    'harga' => $detail['harga'],
                ]);
                DB::table('wood_stocks')
                    ->where('wood_type', $detail['wood_type'])
                    ->where('grade', $detail['grade'])
                    ->where('diameter', $detail['diameter'])
                    ->where('panjang', $detail['panjang'])
                    ->increment('quantity', $detail['pcs']);
            }

            DB::commit();
            return redirect()->route('transaksi.show', $transaksi->id)->with('success', 'Transaksi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Halaman detail transaksi
    public function show($id)
    {
        $transaksi = WoodTransaction::with('details')->findOrFail($id);
        return view('wood_transactions.show', compact('transaksi'));
    }
}