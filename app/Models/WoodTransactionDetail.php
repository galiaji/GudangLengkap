<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoodTransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'wood_transaction_id', 
        'wood_type_id',
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

    public function woodType()
    {
        return $this->belongsTo(WoodType::class, 'wood_type_id');
    }

    // Kolom lama "wood_type" sudah dihapus; accessor wood_type_name mengembalikan
    // NAMA jenis kayu, dipakai di view (nota & laporan).
    public function getWoodTypeNameAttribute()
    {
        return $this->getRelationValue('woodType')?->name;
    }
}
