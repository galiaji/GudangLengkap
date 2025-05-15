<?php

namespace App\Http\Controllers;

use App\Models\WoodTransaction;
use App\Models\WoodTransactionDetail;
use App\Models\WoodStock;
use App\Models\WoodType;
use App\Models\Grade;
use App\Models\Diameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WoodTransactionController extends Controller
{

    public function index()
    {
        $transaksi = WoodTransaction::all();
        return view('wood_transactions.index', compact('transaksi'));
    }

    public function show($id)
    {
        $transaksi = WoodTransaction::findOrFail($id);
        return view('wood_transactions.show', compact('transaksi'));
    }

    public function store(Request $request)
    {
        $transaksi = WoodTransaction::create([
            'atas_nama' => $request->input('atas_nama'),
            'total_volume' => $request->input('total_volume'),
            'total_pembayaran' => $request->input('total_pembayaran'),
        ]);

        foreach ($request->input('detail') as $detail) {
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

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil disimpan!');
    }
}