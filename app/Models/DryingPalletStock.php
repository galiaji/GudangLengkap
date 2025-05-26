<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DryingPalletStock extends Model
{
    use HasFactory;

    protected $table = 'drying_pallet_stocks';

    protected $fillable = [
        'pallet_code',
        'ukuran',
        'pcs',
        'volume',
        'start_drying_date',
        'drying_code', // tambahkan ini
    ];

    public function palletPrice()
    {
        return $this->belongsTo(PalletPrice::class, 'pallet_code', 'pallet_code');
    }
}