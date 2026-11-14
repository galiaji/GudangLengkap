<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Hasil stock opname. Disimpan sekali jalan: saat disimpan, stok langsung disesuaikan
 * dengan hasil hitung fisik (UC-GD-20 log kayu per jenis kayu, UC-GD-21 pallet per batch).
 */
class StockOpname extends Model
{
    protected $fillable = [
        'kode', 'tanggal', 'jenis', 'wood_type_id', 'pallet_status', 'input_code', 'cakupan',
        'status', 'dibuat_oleh', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function woodType()
    {
        return $this->belongsTo(WoodType::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function scopeKayu($query)
    {
        return $query->where('jenis', 'kayu');
    }

    public function scopePalet($query)
    {
        return $query->where('jenis', 'palet');
    }

    // Item yang jumlah fisiknya berbeda dengan stok tercatat
    public function itemSelisih()
    {
        return $this->items->filter(fn ($i) => (int) $i->selisih !== 0);
    }

    public static function nextKode(): string
    {
        $prefix = 'SO-' . now()->format('Ymd') . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderByDesc('kode')->value('kode');
        $urut = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($urut, 2, '0', STR_PAD_LEFT);
    }
}
