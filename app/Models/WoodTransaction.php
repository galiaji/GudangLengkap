<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WoodTransaction extends Model
{
    use HasFactory;

    /**
     * Tabel yang digunakan oleh model ini.
     *
     * @var string
     */
    protected $table = 'wood_transactions';

    protected $fillable = [
        'jenis_transaksi', // Pembelian/Penjualan
        'atas_nama',
        'total_volume',
        'total_pembayaran',
        'tanggal_transaksi'
    ];

    /**
     * Relasi ke model WoodTransactionDetail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(WoodTransactionDetail::class, 'transaksi_id');
    }
}
