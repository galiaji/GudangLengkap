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

    public function wetPalletStocks()
    {
        return $this->hasMany(WetPalletStock::class, 'pallet_code', 'pallet_code');
    }

    public function dryingPalletStocks()
    {
        return $this->hasMany(DryingPalletStock::class, 'pallet_code', 'pallet_code');
    }
}