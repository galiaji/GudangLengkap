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

    
    protected $fillable = ['id_transaksi', 'jenis_transaksi', 'atas_nama', 'volume_m3', 'total_pembayaran', 'tanggal_transaksi'];


    /**
     * Relasi ke model WoodTransactionDetail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(WoodTransactionDetail::class, 'wood_transaction_id');
    }
}
