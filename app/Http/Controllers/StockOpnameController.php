<?php

namespace App\Http\Controllers;

use App\Models\PalletPrice;
use App\Models\PalletStock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\WoodStock;
use App\Models\WoodType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stock opname (khusus admin):
 * - UC-GD-20 log kayu: pilih jenis kayu, isi jumlah batang fisik per grade/diameter/panjang.
 * - UC-GD-21 pallet: pilih batch, isi jumlah pcs fisik untuk semua ukuran terdaftar.
 * Saat disimpan, hasil dicatat dan stok langsung disesuaikan dengan jumlah fisik.
 */
class StockOpnameController extends Controller
{
    private const URUTAN_STATUS = ['basah' => 1, 'penjemuran' => 2, 'kering' => 3];

    private const PESAN_VALIDASI = [
        'fisik.required'   => 'Jumlah fisik wajib diisi.',
        'fisik.*.required' => 'Jumlah fisik wajib diisi untuk semua baris.',
        'fisik.*.integer'  => 'Jumlah fisik harus berupa angka bulat.',
        'fisik.*.min'      => 'Jumlah fisik tidak boleh negatif.',
        'fisik.*.max'      => 'Jumlah fisik terlalu besar.',
        'keterangan.max'   => 'Keterangan maksimal 1000 karakter.',
    ];

    // ================= Riwayat stock opname (menu utama) =================

    // Dua tabel bersebelahan: riwayat log kayu & riwayat pallet (halaman masing-masing terpisah)
    public function index()
    {
        $riwayat = fn (string $jenis) => StockOpname::with('pembuat')
            ->where('jenis', $jenis)
            ->withCount([
                'items',
                'items as items_selisih_count' => fn ($q) => $q->where('selisih', '!=', 0),
            ])
            ->orderByDesc('id')
            ->paginate(15, ['*'], "hal_{$jenis}")
            ->withQueryString();

        return view('stock_opname.index', [
            'kayu'  => $riwayat('kayu'),
            'palet' => $riwayat('palet'),
        ]);
    }

    // ================= UC-GD-20: Stock opname log kayu =================

    public function kayu(Request $request)
    {
        $woodTypes = WoodType::visible()->orderBy('name')->get();
        $woodType  = $request->filled('wood_type_id') ? $woodTypes->firstWhere('id', (int) $request->wood_type_id) : null;

        // Urutan grade mengikuti data stok, sama seperti form pembelian/penjualan log kayu
        $stocks = $woodType
            ? WoodStock::where('wood_type_id', $woodType->id)->orderBy('id')->get()
            : collect();

        // Matriks per grade: baris diameter x kolom panjang (format form pembelian/penjualan)
        $grid = $stocks->groupBy('grade')->map(fn ($rows) => [
            'diameter' => $rows->pluck('diameter')->unique()->sort()->values(),
            'panjang'  => $rows->pluck('panjang')->unique()->sort()->values(),
            'sel'      => $rows->keyBy(fn ($s) => $s->diameter . '|' . $s->panjang),
        ]);

        return view('stock_opname.kayu', compact('woodTypes', 'woodType', 'stocks', 'grid'));
    }

    public function storeKayu(Request $request)
    {
        $data = $request->validate([
            'wood_type_id' => 'required|exists:wood_types,id,is_hidden,0',
            'fisik'        => 'required|array',
            'fisik.*'      => 'required|integer|min:0|max:1000000',
            'sistem'       => 'array',
            'keterangan'   => 'nullable|string|max:1000',
        ], self::PESAN_VALIDASI + [
            'wood_type_id.required' => 'Jenis kayu wajib dipilih.',
            'wood_type_id.exists'   => 'Jenis kayu tidak valid atau sedang disembunyikan.',
        ]);

        $woodType = WoodType::findOrFail($data['wood_type_id']);

        $opname = DB::transaction(function () use ($data, $woodType) {
            $stocks = WoodStock::with('price')
                ->where('wood_type_id', $woodType->id)
                ->orderBy('grade')->orderBy('diameter')->orderBy('panjang')
                ->lockForUpdate()
                ->get();

            $this->pastikanLengkapDanTerbaru(
                $stocks->mapWithKeys(fn ($s) => [$s->id => (int) ($s->quantity ?? 0)]), $data
            );

            $opname = $this->buatOpname($data, [
                'jenis'        => 'kayu',
                'wood_type_id' => $woodType->id,
                'cakupan'      => 'Kayu ' . $woodType->name,
            ]);

            foreach ($stocks as $stock) {
                $item = $this->simpanItem($opname, [
                    'wood_stock_id'  => $stock->id,
                    'label'          => "{$stock->grade} · Ø{$stock->diameter} · P{$stock->panjang}",
                    'volume_per_pcs' => (float) $stock->volume,
                    'harga'          => (float) ($stock->price->harga_beli_per_m3 ?? 0),
                    'qty_sistem'     => (int) ($stock->quantity ?? 0),
                    'qty_fisik'      => (int) $data['fisik'][$stock->id],
                ]);

                // Stok log kayu = jumlah fisik (hanya yang selisih)
                if ($item->selisih !== 0) {
                    $stock->quantity     = $item->qty_fisik;
                    $stock->total_volume = round($item->qty_fisik * $stock->volume, 3);
                    $stock->save();
                }
            }

            return $opname;
        });

        return $this->selesai($opname);
    }

    // ================= UC-GD-21: Stock opname pallet per batch =================

    public function palet(Request $request)
    {
        $batches = PalletStock::select(
                'input_code', 'status', 'tanggal_input',
                DB::raw('COUNT(*) as jumlah_ukuran'), DB::raw('SUM(pcs) as total_pcs')
            )
            ->groupBy('input_code', 'status', 'tanggal_input')
            ->get()
            ->sortBy(fn ($b) => [self::URUTAN_STATUS[$b->status] ?? 9, $b->tanggal_input, $b->input_code])
            ->values();

        $inputCode = $request->filled('input_code') ? (string) $request->input_code : null;
        $batch     = $inputCode ? $batches->firstWhere('input_code', $inputCode) : null;
        $baris     = $batch ? $this->barisBatch(PalletStock::where('input_code', $inputCode)->get()) : collect();

        return view('stock_opname.palet', compact('batches', 'batch', 'baris'));
    }

    public function storePalet(Request $request)
    {
        $data = $request->validate([
            'input_code' => 'required|string|exists:pallet_stocks,input_code',
            'fisik'      => 'required|array',
            'fisik.*'    => 'required|integer|min:0|max:1000000',
            'sistem'     => 'array',
            'keterangan' => 'nullable|string|max:1000',
        ], self::PESAN_VALIDASI + [
            'input_code.required' => 'Batch wajib dipilih.',
            'input_code.exists'   => 'Batch tidak ditemukan atau stoknya sudah habis.',
        ]);

        $opname = DB::transaction(function () use ($data) {
            $rows  = PalletStock::where('input_code', $data['input_code'])->lockForUpdate()->get();
            $baris = $this->barisBatch($rows);
            $acuan = $rows->first(); // status & tanggal batch, dipakai untuk ukuran yang baru ditemukan

            $this->pastikanLengkapDanTerbaru($baris->mapWithKeys(fn ($b) => [$b->kode => $b->sistem]), $data);

            $opname = $this->buatOpname($data, [
                'jenis'         => 'palet',
                'pallet_status' => $acuan->status,
                'input_code'    => $data['input_code'],
                'cakupan'       => "Batch {$data['input_code']} (" . ucfirst($acuan->status) . ')',
            ]);

            foreach ($baris as $b) {
                $item = $this->simpanItem($opname, [
                    'pallet_code'    => $b->kode,
                    'input_code'     => $data['input_code'],
                    'label'          => $b->ukuran,
                    'volume_per_pcs' => $b->volume_per_pcs,
                    'harga'          => $b->harga,
                    'qty_sistem'     => $b->sistem,
                    'qty_fisik'      => (int) $data['fisik'][$b->kode],
                ]);

                if ($item->selisih === 0) {
                    continue;
                }

                $volume = round($item->qty_fisik * $b->volume_per_pcs, 3);
                if ($b->row && $item->qty_fisik === 0) {
                    $b->row->delete();                       // ukuran ini sudah tidak ada di batch
                } elseif ($b->row) {
                    $b->row->update(['pcs' => $item->qty_fisik, 'volume' => $volume]);
                } else {
                    PalletStock::create([                    // ukuran yang belum terinput di batch
                        'pallet_code'   => $b->kode,
                        'ukuran'        => $b->ukuran,
                        'pcs'           => $item->qty_fisik,
                        'volume'        => $volume,
                        'status'        => $acuan->status,
                        'input_code'    => $data['input_code'],
                        'tanggal_input' => $acuan->tanggal_input,
                    ]);
                }
            }

            return $opname;
        });

        return $this->selesai($opname);
    }

    // ================= Detail hasil =================

    public function show(StockOpname $opname)
    {
        $opname->load(['items', 'pembuat', 'woodType']);

        return view('stock_opname.show', compact('opname'));
    }

    // ================= Bantuan =================

    /**
     * Semua ukuran pallet terdaftar untuk satu batch (yang belum ada di batch: stok tercatat 0),
     * ditambah ukuran di batch yang tidak ada di daftar harga. Diurutkan sesuai daftar ukuran.
     */
    private function barisBatch(Collection $rows): Collection
    {
        $status    = $rows->first()->status ?? 'basah';
        $byCode    = $rows->keyBy('pallet_code');
        $buatBaris = fn ($kode, $ukuran, $row, ?PalletPrice $p) => (object) [
            'kode'           => $kode,
            'ukuran'         => $ukuran,
            'status'         => $status,
            'row'            => $row,
            'sistem'         => (int) ($row->pcs ?? 0),
            'volume_per_pcs' => $p ? $p->volumeM3PerPcs() : ($row && $row->pcs > 0 ? $row->volume / $row->pcs : 0),
            // Basah & penjemuran pakai harga basah (masih basah), kering pakai harga kering
            'harga'          => $p ? (float) ($status === 'kering' ? $p->harga_kering : $p->harga_basah) : 0,
        ];

        $prices = PalletPrice::orderBy('id')->get();
        $baris  = $prices->map(fn ($p) => $buatBaris($p->pallet_code, $p->ukuran, $byCode[$p->pallet_code] ?? null, $p));

        $lain = $rows->whereNotIn('pallet_code', $prices->pluck('pallet_code'))
            ->map(fn ($r) => $buatBaris($r->pallet_code, $r->ukuran, $r, null));

        return $baris->concat($lain)->values();
    }

    /**
     * Semua baris wajib diisi, dan stok tercatat tidak boleh berubah sejak halaman dibuka
     * (misalnya ada transaksi di antaranya). Jika berubah, pengguna diminta memuat ulang.
     *
     * @param Collection $sistem [kunci isian => jumlah stok tercatat saat ini]
     */
    private function pastikanLengkapDanTerbaru(Collection $sistem, array $data): void
    {
        $kurang = $sistem->keys()->reject(fn ($k) => array_key_exists($k, $data['fisik']))->count();
        if ($sistem->isEmpty() || $kurang > 0) {
            throw ValidationException::withMessages([
                'fisik' => $sistem->isEmpty()
                    ? 'Tidak ada stok yang bisa dihitung.'
                    : "Masih ada {$kurang} baris yang belum diisi. Isi 0 jika barangnya tidak ada.",
            ]);
        }

        $berubah = $sistem->filter(fn ($qty, $k) => isset($data['sistem'][$k]) && (int) $data['sistem'][$k] !== $qty);
        if ($berubah->isNotEmpty()) {
            throw ValidationException::withMessages([
                'fisik' => 'Stok tercatat berubah sejak halaman dibuka (ada transaksi baru). '
                    . 'Muat ulang halaman untuk melihat stok terbaru, lalu periksa kembali hasil hitung.',
            ]);
        }
    }

    private function buatOpname(array $data, array $atribut): StockOpname
    {
        return StockOpname::create($atribut + [
            'kode'        => StockOpname::nextKode(),
            'tanggal'     => now()->toDateString(),
            'status'      => 'selesai',
            'dibuat_oleh' => auth()->id(),
            'catatan'     => $data['keterangan'] ?? null,
        ]);
    }

    // Simpan satu baris hasil hitung beserta selisih jumlah, volume (m³), dan nilai
    private function simpanItem(StockOpname $opname, array $atribut): StockOpnameItem
    {
        $selisih = $atribut['qty_fisik'] - $atribut['qty_sistem'];
        $volume  = $selisih * $atribut['volume_per_pcs'];

        return $opname->items()->create($atribut + [
            'selisih'        => $selisih,
            'volume_selisih' => round($volume, 3),
            'nilai_selisih'  => round($volume * $atribut['harga'], 2),
        ]);
    }

    private function selesai(StockOpname $opname)
    {
        $jumlah = $opname->items()->where('selisih', '!=', 0)->count();
        $pesan  = $jumlah > 0
            ? "Stock opname {$opname->kode} disimpan. {$jumlah} item selisih, stok sudah disesuaikan dengan hasil hitung fisik."
            : "Stock opname {$opname->kode} disimpan. Tidak ada selisih, stok tidak berubah.";

        return redirect()->route('stock-opname.show', $opname)->with('success', $pesan);
    }
}
