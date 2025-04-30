<?php

namespace App\Http\Controllers;

use App\Models\WoodTransaction;
use App\Models\WoodType;
use App\Models\Grade;
use App\Models\Diameter;
use Illuminate\Http\Request;

class WoodTransactionController extends Controller
{
    /**
     * Menampilkan halaman pembelian stok.
     */
    public function buy()
    {
        $woodTypes = WoodType::all();
        $grades = Grade::all();
        $diameters = Diameter::all(); // Data volume berdasarkan diameter dan panjang
        $woodPrices = \App\Models\WoodPrice::all(); // Data harga kayu berdasarkan jenis dan grade

        return view('wood_transactions.buy', compact('woodTypes', 'grades', 'diameters', 'woodPrices'));
    }

    /**
     * Menyimpan data pembelian stok.
     */
    public function storeBuy(Request $request)
    {
        $request->validate([
            'jenis_kayu_id' => 'required|exists:wood_types,id',
            'grade_id' => 'required|exists:grades,id',
            'diameter' => 'required|integer|min:1',
            'panjang' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'volume' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
        ]);

        WoodTransaction::create($request->all());

        return redirect()->route('wood_transactions.buy')->with('success', 'Stok berhasil ditambahkan.');
    }

    /**
     * Menampilkan halaman penjualan stok.
     */
    public function sell()
    {
        $woodTransactions = WoodTransaction::all();
        return view('wood_transactions.sell', compact('woodTransactions'));
    }

    /**
     * Menyimpan data penjualan stok.
     */
    public function storeSell(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:wood_transactions,id',
            'diameter' => 'required|integer|min:1',
            'panjang' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
        ]);

        $transaction = WoodTransaction::findOrFail($request->id);

        if ($transaction->quantity < $request->quantity) {
            return redirect()->back()->withErrors(['quantity' => 'Jumlah stok tidak mencukupi.']);
        }

        $transaction->quantity -= $request->quantity;
        $transaction->save();

        return redirect()->route('wood_transactions.sell')->with('success', 'Stok berhasil dikurangi.');
    }
}
