<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalletSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi', 'tanggal', 'customer', 'keterangan'
    ];

    public function items()
    {
        return $this->hasMany(PalletSaleItem::class);
    }
}
