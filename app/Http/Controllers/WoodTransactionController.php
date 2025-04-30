<?php

namespace App\Http\Controllers;

use App\Models\WoodTransaction;
use App\Models\WoodType;
use App\Models\Grade;
use App\Models\Diameter;
use Illuminate\Http\Request;
use App\Models\WoodTransactionDetail;
use App\Models\WoodStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


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
            'seller_name' => 'required|string',
            'details' => 'required|array',
            'details.*.jenis_kayu_id' => 'required|exists:wood_types,id',
            'details.*.grade_id' => 'required|exists:grades,id',
            'details.*.diameter' => 'required|integer|min:1',
            'details.*.panjang' => 'required|integer|min:1',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.total_volume' => 'required|numeric|min:0',
        ]);

        \Log::info('Request Data:', $request->all()); // Debug input data

        DB::beginTransaction();

        try {
            // Simpan transaksi utama
            $transaction = WoodTransaction::create([
                'seller_name' => $request->seller_name,
                'grand_total_volume' => collect($request->details)->sum('total_volume'),
                'grand_total_price' => collect($request->details)->sum(function ($detail) {
                    return $detail['quantity'] * $detail['total_volume'];
                }),
            ]);

            \Log::info('Transaction Created:', $transaction->toArray()); // Debug transaksi

            // Simpan detail transaksi
            foreach ($request->details as $detail) {
                $transactionDetail = WoodTransactionDetail::create([
                    'wood_transaction_id' => $transaction->id,
                    'jenis_kayu_id' => $detail['jenis_kayu_id'],
                    'grade_id' => $detail['grade_id'],
                    'volume' => $detail['total_volume'],
                    'price' => $detail['quantity'] * $detail['total_volume'],
                ]);

                \Log::info('Transaction Detail Created:', $transactionDetail->toArray()); // Debug detail transaksi

                // Perbarui stok
                $stock = WoodStock::firstOrNew([
                    'wood_type_id' => $detail['jenis_kayu_id'],
                    'grade_id' => $detail['grade_id'],
                    'diameter' => $detail['diameter'],
                    'panjang' => $detail['panjang'],
                ]);
                $stock->quantity += $detail['quantity'];
                $stock->save();

                \Log::info('Stock Updated:', $stock->toArray()); // Debug stok
            }

            DB::commit();

            return redirect()->route('wood_transactions.buy')->with('success', 'Transaksi berhasil disimpan dan stok diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error:', ['exception' => $e->getMessage()]); // Debug error
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan transaksi.']);
        }
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

        DB::beginTransaction();

        try {
            $transaction = WoodTransaction::findOrFail($request->id);

            // Periksa stok
            $stock = WoodStock::where([
                'wood_type_id' => $transaction->wood_type_id,
                'grade_id' => $transaction->grade_id,
                'diameter' => $request->diameter,
                'panjang' => $request->panjang,
            ])->first();

            if (!$stock || $stock->quantity < $request->quantity) {
                return redirect()->back()->withErrors(['quantity' => 'Jumlah stok tidak mencukupi.']);
            }

            // Kurangi stok
            $stock->quantity -= $request->quantity;
            $stock->save();

            DB::commit();

            return redirect()->route('wood_transactions.sell')->with('success', 'Stok berhasil dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat mengurangi stok.']);
        }
    }
}
