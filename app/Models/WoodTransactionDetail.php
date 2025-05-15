<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoodTransactionDetail extends Model
{
    use HasFactory;

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'wood_transaction_id', // Relasi ke tabel wood_transactions
        'jenis_kayu_id',       // Relasi ke tabel wood_types
        'grade_id',            // Relasi ke tabel grades
        'diameter',            // Diameter kayu
        'panjang',             // Panjang kayu
        'quantity',            // Jumlah kayu
        'volume',              // Volume per pcs
//'price_per_m3',        // Harga per m³
        'subtotal_volume',     // Subtotal volume
        'subtotal_price',      // Subtotal harga
    ];

    /**
     * Relasi ke model WoodTransaction (tabel wood_transactions)
     */
    public function woodTransaction()
    {
        return $this->belongsTo(WoodTransaction::class, 'wood_transaction_id');
    }

    /**
     * Relasi ke model WoodType (tabel wood_types)
     */
    public function woodType()
    {
        return $this->belongsTo(WoodType::class, 'jenis_kayu_id');
    }

    /**
     * Relasi ke model Grade (tabel grades)
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }
}
