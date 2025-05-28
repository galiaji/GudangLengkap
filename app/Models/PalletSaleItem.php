<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalletSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pallet_sale_id', 'pallet_code', 'ukuran', 'status', 'pcs', 'volume', 'harga_satuan', 'subtotal'
    ];

    public function sale()
    {
        return $this->belongsTo(PalletSale::class, 'pallet_sale_id');
    }
}
