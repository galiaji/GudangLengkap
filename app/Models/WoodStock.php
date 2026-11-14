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

    /**
     * Volume (m³) & harga satu baris transaksi, dihitung dari data stok, bukan dari browser.
     * Harga selalu per m³: pcs x volume per batang x harga per m³.
     *
     * @param string $kolomHarga 'harga_beli_per_m3' atau 'harga_jual_per_m3'
     * @return array{0: float, 1: float} [volume, harga]
     */
    public function hitungBaris(int $pcs, string $kolomHarga): array
    {
        $volume  = round($pcs * (float) $this->volume, 3);
        $hargaM3 = (float) ($this->price->{$kolomHarga} ?? 0);

        return [$volume, round($volume * $hargaM3, 2)];
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
