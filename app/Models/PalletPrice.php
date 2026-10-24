<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalletPrice extends Model
{
    use HasFactory;

    protected $table = 'pallet_prices';

    protected $fillable = [
        'pallet_code',
        'ukuran',
        'volume_per_pcs',
        'harga_basah',
        'harga_kering',
    ];

    /**
     * Volume per pcs dari teks ukuran "T x L x P" (cm).
     * Satuannya mengikuti tabel lama: (T x L x P) / 1000,
     * contoh "8x1.2x100" => 0.96.
     */
    public static function volumeFromUkuran(string $ukuran): float
    {
        $parts = array_map('floatval', explode('x', strtolower(str_replace(' ', '', $ukuran))));
        if (count($parts) !== 3) {
            return 0;
        }
        return round($parts[0] * $parts[1] * $parts[2] / 1000, 6);
    }

    // Pakai nilai tersimpan; kalau kosong, hitung dari ukuran
    public function getVolumePerPcsAttribute($value): float
    {
        return $value !== null ? (float) $value : self::volumeFromUkuran((string) $this->ukuran);
    }

    /**
     * Kode palet berikutnya: A1, A2, ... => A{angka terbesar + 1}
     */
    public static function nextCode(): string
    {
        $max = self::pluck('pallet_code')
            ->map(fn ($code) => preg_match('/(\d+)$/', $code, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        return 'A' . ($max + 1);
    }

    /**
     * Gabungkan dimensi menjadi teks ukuran, contoh (8, 1.2, 100) => "8x1.2x100".
     */
    public static function formatUkuran($tebal, $lebar, $panjang): string
    {
        $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
        return $fmt($tebal) . 'x' . $fmt($lebar) . 'x' . $fmt($panjang);
    }

    public function palletStocks()
    {
        return $this->hasMany(PalletStock::class, 'pallet_code', 'pallet_code');
    }
    public function saleItems()
    {
        return $this->hasMany(PalletSaleItem::class, 'pallet_code', 'pallet_code');
    }
}