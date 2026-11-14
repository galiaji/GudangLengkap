<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id', 'wood_stock_id', 'pallet_code', 'input_code', 'label', 'volume_per_pcs', 'harga',
        'qty_sistem', 'qty_fisik', 'selisih', 'volume_selisih', 'nilai_selisih', 'alasan', 'keterangan',
    ];

    protected $casts = [
        'qty_sistem'     => 'integer',
        'qty_fisik'      => 'integer',
        'selisih'        => 'integer',
        'volume_per_pcs' => 'float',
        'harga'          => 'float',
        'volume_selisih' => 'float',
        'nilai_selisih'  => 'float',
    ];

    public function opname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function woodStock()
    {
        return $this->belongsTo(WoodStock::class);
    }
}
