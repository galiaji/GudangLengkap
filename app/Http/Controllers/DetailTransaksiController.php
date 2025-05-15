<?php

namespace App\Http\Controllers;

use App\Models\WoodTransactionDetail;
use Illuminate\Http\Request;

class DetailTransaksiController extends Controller
{
    // Menampilkan semua detail transaksi (opsional, biasanya tidak perlu)
    public function index()
    {
        $details = WoodTransactionDetail::all();
        return response()->json($details);
    }

    // Menampilkan satu detail transaksi
    public function show($id)
    {
        $detail = WoodTransactionDetail::findOrFail($id);
        return response()->json($detail);
    }

    // Tambahkan method lain sesuai kebutuhan (store, update, destroy, dll)
}