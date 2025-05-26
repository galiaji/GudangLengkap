<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WetPalletStock extends Model
{
    use HasFactory;

    protected $table = 'wet_pallet_stocks';

    protected $fillable = [
        'pallet_code',
        'ukuran',
        'pcs',
        'volume',
        'entry_date',
    ];

    public function palletPrice()
    {
        return $this->belongsTo(PalletPrice::class, 'pallet_code', 'pallet_code');
    }
}