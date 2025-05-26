<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalletStock extends Model
{
    use HasFactory;

    protected $table = 'pallet_stocks';

    protected $fillable = [
        'pallet_code',
        'ukuran',
        'pcs',
        'volume',
        'status',
        'input_code',
        'tanggal_input',
    ];

    public function palletPrice()
    {
        return $this->belongsTo(PalletPrice::class, 'pallet_code', 'pallet_code');
    }
}
