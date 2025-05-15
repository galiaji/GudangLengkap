<?php

namespace App\Http\Controllers;

use App\Models\WoodTransaction;
use App\Models\WoodTransactionDetail;
use App\Models\WoodType;
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
        $jenisKayu = WoodType::pluck('name')->toArray();
        return view('wood_transactions.buy', compact('jenisKayu'));
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
            'details.*.jenis_kayu' => 'required|string',
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
                    'transaksi_id' => $transaksi->id,
                    'jenis_kayu' => $detail['jenis_kayu'],
                    'grade' => $detail['grade'],
                    'diameter' => $detail['diameter'],
                    'panjang' => $detail['panjang'],
                    'pcs' => $detail['pcs'],
                    'volume' => $detail['volume'],
                    'harga' => $detail['harga'],
                ]);
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