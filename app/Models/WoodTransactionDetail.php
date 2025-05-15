<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoodTransactionDetail extends Model
{
    use HasFactory;

     protected $fillable = [
        'transaksi_id', 'jenis_kayu', 'grade', 'diameter', 'panjang', 'pcs', 'volume', 'harga'
    ];

    public function transaksi()
    {
        return $this->belongsTo(WoodTransaction::class, 'transaksi_id');
    }
}
