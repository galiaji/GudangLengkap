<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WoodStock extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'wood_stocks';

    /**
     * @var array
     */
    protected $fillable = [
        'wood_type',   
        'grade',       
        'diameter',
        'panjang',
        'quantity',
        'total_volume',
        'volume',
    ];

    public function price()
    {
        return $this->belongsTo(WoodPrice::class, 'wood_price_id');
    }
     public function transactionDetails() {
        return $this->hasMany(WoodTransactionDetail::class, 'wood_stock_id');
    }
}
