<?php

namespace App\Http\Controllers;
use App\Models\WoodTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class DetailTransaksiController extends Controller
{
    use HasFactory;
    
    protected $fillable = ['transaksi_id', 'jenis_kayu', 'grade', 'diameter', 'panjang', 'pcs', 'volume', 'harga'];

    public function transaksi()
    {
        return $this->belongsTo(WoodTransaction::class);
    }
}