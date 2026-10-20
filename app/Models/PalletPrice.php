<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalletPrice extends Model
{
    use HasFactory;

    protected $table = 'pallet_prices';

    protected $fillable = [
        'pallet_code',
        'ukuran',
        'harga_basah',
        'harga_kering',
    ];

    public function palletStocks()
    {
        return $this->hasMany(PalletStock::class, 'pallet_code', 'pallet_code');
    }
    public function saleItems()
    {
        return $this->hasMany(PalletSaleItem::class, 'pallet_code', 'pallet_code');
    }
}