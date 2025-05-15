<?php

namespace App\Http\Controllers;

use App\Models\WoodTransaction;
use App\Models\WoodTransactionDetail;
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
        $grades = [
            'Rijek 1' => [10, 11, 12, 14, 15],
            'Rijek 2' => [16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30],
            'Storty 1' => [13, 14, 15],
            'Storty 2' => [16, 17, 18],
            'Super 1' => [19, 20, 21, 22, 23, 24],
            'Super 2' => [25, 26, 27, 28, 29, 30],
        ];

        $panjang = [100, 130, 200, 260];

        $allowedLengths = [
            'Rijek' => [100, 130],
            'Storty' => [130],
            'Super' => [130, 200],
        ];

        $jenisKayu = 'Mahoni'; // atau bisa array jika multiple

        return view('wood_transactions.buy', compact(
            'grades', 'panjang', 'jenisKayu', 'allowedLengths'
        ));
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