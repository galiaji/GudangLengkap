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
        'wood_type_id',   
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
    public function woodType()
    {
        return $this->belongsTo(WoodType::class, 'wood_type_id');
    }

    // Nama jenis kayu (pengganti kolom lama "wood_type")
    public function getWoodTypeNameAttribute()
    {
        return $this->getRelationValue('woodType')?->name;
    }
     public function transactionDetails() {
        return $this->hasMany(WoodTransactionDetail::class, 'wood_stock_id');
    }
}
