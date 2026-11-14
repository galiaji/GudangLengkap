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
     * Volume per pcs dalam m³. volume_per_pcs disimpan dalam (T x L x P) / 1000,
     * jadi perlu dibagi 1000 lagi, contoh 8x1.2x100 => 0.96 / 1000 = 0.00096 m³.
     * Dipakai untuk volume stok, penjualan, dan stock opname (harga palet per m³).
     */
    public function volumeM3PerPcs(): float
    {
        return $this->volume_per_pcs / 1000;
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
     * Gabungkan dimensi menjadi teks ukuran lebar x tebal x panjang,
     * contoh lebar 8, tebal 1.2, panjang 100 => "8x1.2x100".
     */
    public static function formatUkuran($lebar, $tebal, $panjang): string
    {
        $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
        return $fmt($lebar) . 'x' . $fmt($tebal) . 'x' . $fmt($panjang);
    }

    /**
     * Cari ukuran terdaftar yang sama walaupun urutan dimensinya berbeda
     * (8x1.2x100, 1.2x8x100, dan 1.2x100x8 dianggap sama). Mengembalikan teks ukuran lama atau null.
     */
    public static function ukuranSama($tebal, $lebar, $panjang): ?string
    {
        $kunci = function ($t, $l, $p) {
            $d = [round((float) $t, 2), round((float) $l, 2), round((float) $p, 2)];
            sort($d);
            return implode('|', $d);
        };
        $dicari = $kunci((float) $tebal, (float) $lebar, (float) $panjang);

        return self::pluck('ukuran')->first(function ($ukuran) use ($kunci, $dicari) {
            $d = array_map(
                fn ($n) => (float) str_replace(',', '.', $n),
                explode('x', strtolower(str_replace(' ', '', (string) $ukuran)))
            );
            return count($d) === 3 && $kunci($d[0], $d[1], $d[2]) === $dicari;
        });
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