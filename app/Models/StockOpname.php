<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    // Status yang membuat stok terkunci
    public const STATUS_AKTIF = ['menghitung', 'menunggu'];

    public const STATUS_PALET = ['basah', 'penjemuran', 'kering'];

    public const ALASAN = ['Rusak / busuk', 'Hilang', 'Salah catat', 'Susut', 'Lainnya'];

    protected $fillable = [
        'kode', 'tanggal', 'jenis', 'wood_type_id', 'pallet_status', 'cakupan',
        'status', 'dibuat_oleh', 'disetujui_oleh', 'disetujui_at', 'catatan',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'disetujui_at' => 'datetime',
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

    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereIn('status', self::STATUS_AKTIF);
    }

    public function isAktif(): bool
    {
        return in_array($this->status, self::STATUS_AKTIF, true);
    }

    public function statusLabel(): string
    {
        return [
            'menghitung' => 'Menghitung',
            'menunggu'   => 'Menunggu persetujuan',
            'selesai'    => 'Selesai',
            'batal'      => 'Dibatalkan',
        ][$this->status] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return [
            'menghitung' => 'bg-warning text-dark',
            'menunggu'   => 'bg-info text-dark',
            'selesai'    => 'bg-success',
            'batal'      => 'bg-secondary',
        ][$this->status] ?? 'bg-secondary';
    }

    /**
     * Pesan kunci jika stok jenis kayu ini sedang di-opname, atau null jika bebas.
     */
    public static function kunciKayu($woodTypeId): ?string
    {
        $opname = self::aktif()->where('jenis', 'kayu')->where('wood_type_id', $woodTypeId)->first();

        return $opname ? self::pesanKunci($opname) : null;
    }

    /**
     * Pesan kunci jika stok palet dengan status ini sedang di-opname, atau null jika bebas.
     */
    public static function kunciPalet(?string $status): ?string
    {
        $opname = self::aktif()->where('jenis', 'palet')->where('pallet_status', $status)->first();

        return $opname ? self::pesanKunci($opname) : null;
    }

    private static function pesanKunci(self $opname): string
    {
        return "Stok {$opname->cakupan} sedang dalam stock opname {$opname->kode}. "
            . 'Transaksi untuk stok ini dikunci sampai opname selesai atau dibatalkan.';
    }

    public static function nextKode(): string
    {
        $prefix = 'SO-' . now()->format('Ymd') . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderByDesc('kode')->value('kode');
        $urut = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($urut, 2, '0', STR_PAD_LEFT);
    }
}
