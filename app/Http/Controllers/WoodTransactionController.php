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
            'seller_name' => 'required|string|max:255',
            'transaction_total_volume' => 'required|numeric|min:0',
            'transaction_total_price' => 'required|numeric|min:0',
            'details.*.jenis_kayu_id' => 'required|integer|exists:wood_types,id',
            'details.*.grade_id' => 'required|integer|exists:grades,id',
            'details.*.diameter' => 'required|numeric|min:0',
            'details.*.panjang' => 'required|integer|min:0',
            'details.*.volume' => 'required|numeric|min:0',
            'details.*.price' => 'required|numeric|min:0',
            'details.*.quantity' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Simpan data transaksi ke tabel wood_transactions
            $transaction = WoodTransaction::create([
                'seller_name' => $request->seller_name,
                'grand_total_volume' => $request->transaction_total_volume,
                'grand_total_price' => $request->transaction_total_price,
            ]);

            // Simpan detail transaksi ke tabel wood_transaction_details
            foreach ($request->details as $detail) {
                WoodTransactionDetail::create([
                    'wood_transaction_id' => $transaction->id,
                    'jenis_kayu_id' => $detail['jenis_kayu_id'],
                    'grade_id' => $detail['grade_id'],
                    'diameter' => $detail['diameter'],
                    'panjang' => $detail['panjang'],
                    'quantity' => $detail['quantity'],
                    'volume' => $detail['volume'],
                    'price_per_m3' => $detail['price'],
                    'subtotal_volume' => $detail['quantity'] * $detail['volume'],
                    'subtotal_price' => $detail['quantity'] * $detail['volume'] * $detail['price'],
                ]);
            }
        });

        return redirect()->route('wood_transactions.buy')->with('success', 'Transaksi berhasil disimpan.');
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
            'jenis_kayu_id' => 'required|exists:wood_types,id',
            'grade_id' => 'required|exists:grades,id',
            'diameter' => 'required|integer|min:1',
            'panjang' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Periksa stok
            $stock = WoodStock::where([
                'wood_type_id' => $request->jenis_kayu_id,
                'grade_id' => $request->grade_id,
                'diameter' => $request->diameter,
                'panjang' => $request->panjang,
            ])->first();

            if (!$stock || $stock->quantity < $request->quantity) {
                return redirect()->back()->withErrors(['quantity' => 'Jumlah stok tidak mencukupi.']);
            }

            // Kurangi stok
            $stock->update([
                'quantity' => $stock->quantity - $request->quantity,
                'total_volume' => $stock->total_volume - ($request->quantity * $stock->volume),
            ]);

            // Simpan transaksi penjualan
            $transactionDetailData = [
                'wood_transaction_id' => $request->id,
                'jenis_kayu_id' => $request->jenis_kayu_id,
                'grade_id' => $request->grade_id,
                'diameter' => $request->diameter,
                'panjang' => $request->panjang,
                'quantity' => $request->quantity,
                'volume' => $stock->volume,
                'price_per_m3' => $stock->price_per_m3,
                'subtotal_volume' => $request->quantity * $stock->volume,
                'subtotal_price' => $request->quantity * $stock->volume * $stock->price_per_m3,
            ];

            WoodTransactionDetail::create($transactionDetailData);

            DB::commit();

            return redirect()->route('wood_transactions.sell')->with('success', 'Stok berhasil dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat mengurangi stok.']);
        }
    }
}