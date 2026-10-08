<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoodTransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'wood_transaction_id', 
        'wood_type',
        'grade',
        'diameter',
        'panjang',
        'pcs',
        'volume',
        'harga',
        'wood_stock_id'
    ];

    public function transaction()
    {
        return $this->belongsTo(WoodTransaction::class, 'wood_transaction_id');
    }
    public function stock() {
        return $this->belongsTo(WoodStock::class, 'wood_stock_id');
    }
}
