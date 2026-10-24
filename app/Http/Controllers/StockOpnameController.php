<?php

namespace App\Http\Controllers;

use App\Models\PalletPrice;
use App\Models\PalletStock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\WoodStock;
use App\Models\WoodType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockOpnameController extends Controller
{
    // Daftar sesi stock opname
    public function index()
    {
        $opnames = StockOpname::with('pembuat')
            ->withCount('items')
            ->withCount(['items as items_selisih_count' => fn ($q) => $q->where('selisih', '!=', 0)])
            ->orderByDesc('id')
            ->get();

        return view('stock_opname.index', compact('opnames'));
    }

    // Form mulai stock opname
    public function create()
    {
        $woodTypes = WoodType::visible()->orderBy('name')->get();
        $aktif     = StockOpname::aktif()->get();

        return view('stock_opname.create', [
            'woodTypes'     => $woodTypes,
            'kayuTerkunci'  => $aktif->where('jenis', 'kayu')->pluck('wood_type_id')->all(),
            'paletTerkunci' => $aktif->where('jenis', 'palet')->pluck('pallet_status')->all(),
        ]);
    }

    /**
     * Mulai stock opname: kunci stok yang dipilih dan simpan jumlah sistem saat ini (snapshot).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis'         => 'required|in:kayu,palet',
            'wood_type_id'  => 'required_if:jenis,kayu|nullable|exists:wood_types,id,is_hidden,0',
            'pallet_status' => ['required_if:jenis,palet', 'nullable', Rule::in(StockOpname::STATUS_PALET)],
            'catatan'       => 'nullable|string|max:1000',
        ], [
            'jenis.required'            => 'Jenis stok wajib dipilih.',
            'wood_type_id.required_if'  => 'Jenis kayu wajib dipilih.',
            'wood_type_id.exists'       => 'Jenis kayu tidak valid atau sedang disembunyikan.',
            'pallet_status.required_if' => 'Status palet wajib dipilih.',
        ]);

        $opname = DB::transaction(function () use ($data) {
            if ($data['jenis'] === 'kayu') {
                $woodType = WoodType::lockForUpdate()->findOrFail($data['wood_type_id']);
                if ($pesan = StockOpname::kunciKayu($woodType->id)) {
                    return $pesan;
                }
                $cakupan = 'Kayu ' . $woodType->name;
            } else {
                if ($pesan = StockOpname::kunciPalet($data['pallet_status'])) {
                    return $pesan;
                }
                $cakupan = 'Palet ' . ucfirst($data['pallet_status']);
            }

            $opname = StockOpname::create([
                'kode'          => StockOpname::nextKode(),
                'tanggal'       => now()->toDateString(),
                'jenis'         => $data['jenis'],
                'wood_type_id'  => $data['jenis'] === 'kayu' ? $data['wood_type_id'] : null,
                'pallet_status' => $data['jenis'] === 'palet' ? $data['pallet_status'] : null,
                'cakupan'       => $cakupan,
                'status'        => 'menghitung',
                'dibuat_oleh'   => auth()->id(),
                'catatan'       => $data['catatan'] ?? null,
            ]);

            $opname->items()->createMany(
                $data['jenis'] === 'kayu' ? $this->snapshotKayu($opname) : $this->snapshotPalet($opname)
            );

            return $opname;
        });

        if (is_string($opname)) {
            return back()->withInput()->with('error', $opname);
        }

        return redirect()->route('stock-opname.show', $opname)
            ->with('success', "Stock opname {$opname->kode} dimulai. Stok {$opname->cakupan} dikunci. Silakan hitung stok fisik.");
    }

    public function show(StockOpname $opname)
    {
        $opname->load(['items', 'pembuat', 'penyetuju']);

        $ringkasan = [
            'jumlah'       => $opname->items->count(),
            'terisi'       => $opname->items->whereNotNull('qty_fisik')->count(),
            'selisih'      => $opname->items->where('selisih', '!=', 0)->whereNotNull('selisih')->count(),
            'kurang'       => $opname->items->where('selisih', '<', 0)->sum('selisih'),
            'lebih'        => $opname->items->where('selisih', '>', 0)->sum('selisih'),
            'nilai'        => $opname->items->sum('nilai_selisih'),
            'volume'       => $opname->items->sum('volume_selisih'),
        ];

        return view('stock_opname.show', [
            'opname'    => $opname,
            'ringkasan' => $ringkasan,
            'alasanList' => StockOpname::ALASAN,
        ]);
    }

    /**
     * Simpan hasil hitung fisik. Tombol "ajukan" juga mengirim ke admin untuk diperiksa.
     */
    public function simpanHitung(Request $request, StockOpname $opname)
    {
        if ($opname->status !== 'menghitung') {
            return back()->with('error', 'Hasil hitung hanya bisa diubah saat status opname "Menghitung".');
        }

        $request->validate([
            'fisik'   => 'array',
            'fisik.*' => 'nullable|integer|min:0|max:1000000',
        ], [
            'fisik.*.integer' => 'Jumlah fisik harus bilangan bulat.',
            'fisik.*.min'     => 'Jumlah fisik tidak boleh negatif.',
        ]);

        $fisik  = $request->input('fisik', []);
        $ajukan = $request->input('aksi') === 'ajukan';

        DB::transaction(function () use ($opname, $fisik) {
            foreach ($opname->items as $item) {
                if (!array_key_exists($item->id, $fisik)) {
                    continue;
                }
                $nilai = $fisik[$item->id];
                $item->qty_fisik = ($nilai === null || $nilai === '') ? null : (int) $nilai;
                $this->hitungSelisih($item);
                $item->save();
            }
        });

        if (!$ajukan) {
            return back()->with('success', 'Hasil hitung disimpan sementara.');
        }

        $kosong = $opname->items()->whereNull('qty_fisik')->count();
        if ($kosong > 0) {
            return back()->with('error', "Masih ada {$kosong} item yang belum diisi jumlah fisiknya. Isi 0 jika barangnya memang tidak ada.");
        }

        $opname->update(['status' => 'menunggu']);

        return redirect()->route('stock-opname.show', $opname)
            ->with('success', 'Hasil hitung diajukan. Menunggu pemeriksaan dan persetujuan admin.');
    }

    // Admin mengembalikan opname untuk dihitung ulang
    public function kembalikan(StockOpname $opname)
    {
        if ($opname->status !== 'menunggu') {
            return back()->with('error', 'Hanya opname yang menunggu persetujuan yang bisa dikembalikan.');
        }

        $opname->update(['status' => 'menghitung']);

        return redirect()->route('stock-opname.show', $opname)
            ->with('success', 'Opname dikembalikan untuk dihitung ulang. Stok tetap terkunci.');
    }

    /**
     * Admin menyetujui: stok disesuaikan dengan jumlah fisik, lalu kunci dilepas.
     */
    public function setujui(Request $request, StockOpname $opname)
    {
        if ($opname->status !== 'menunggu') {
            return back()->with('error', 'Hanya opname yang menunggu persetujuan yang bisa disetujui.');
        }

        $itemSelisih = $opname->items()->where('selisih', '!=', 0)->get();

        $rules = [];
        foreach ($itemSelisih as $item) {
            $rules["alasan.{$item->id}"]     = ['required', Rule::in(StockOpname::ALASAN)];
            $rules["keterangan.{$item->id}"] = 'nullable|string|max:255';
        }
        $request->validate($rules, [
            'alasan.*.required' => 'Alasan wajib dipilih untuk setiap item yang selisih.',
            'alasan.*.in'       => 'Alasan tidak valid.',
        ]);

        DB::transaction(function () use ($request, $opname, $itemSelisih) {
            foreach ($itemSelisih as $item) {
                $item->alasan     = $request->input("alasan.{$item->id}");
                $item->keterangan = $request->input("keterangan.{$item->id}");
                $item->save();
            }

            if ($opname->jenis === 'kayu') {
                $this->sesuaikanKayu($opname);
            } else {
                $this->sesuaikanPalet($opname, $itemSelisih);
            }

            $opname->update([
                'status'         => 'selesai',
                'disetujui_oleh' => auth()->id(),
                'disetujui_at'   => now(),
            ]);
        });

        return redirect()->route('stock-opname.show', $opname)
            ->with('success', "Stock opname {$opname->kode} disetujui. Stok sudah disesuaikan dan kunci dilepas.");
    }

    // Admin membatalkan opname: stok tidak diubah, kunci dilepas
    public function batal(StockOpname $opname)
    {
        if (!$opname->isAktif()) {
            return back()->with('error', 'Opname ini sudah tidak aktif.');
        }

        $opname->update(['status' => 'batal']);

        return redirect()->route('stock-opname.show', $opname)
            ->with('success', "Stock opname {$opname->kode} dibatalkan. Stok tidak diubah dan kunci dilepas.");
    }

    // Lembar hitung untuk petugas (tanpa angka stok sistem)
    public function lembar(StockOpname $opname)
    {
        $opname->load('items');

        return Pdf::loadView('exports.opname_lembar', compact('opname'))
            ->setPaper('a4', 'portrait')
            ->download("lembar-hitung-{$opname->kode}.pdf");
    }

    // Laporan hasil stock opname
    public function laporan(StockOpname $opname)
    {
        $opname->load(['items', 'pembuat', 'penyetuju']);

        return Pdf::loadView('exports.opname_laporan', compact('opname'))
            ->setPaper('a4', 'landscape')
            ->download("laporan-{$opname->kode}.pdf");
    }

    // ---------- Bantuan ----------

    private function snapshotKayu(StockOpname $opname): array
    {
        return WoodStock::with('price')
            ->where('wood_type_id', $opname->wood_type_id)
            ->orderBy('grade')->orderBy('diameter')->orderBy('panjang')
            ->lockForUpdate()
            ->get()
            ->map(fn ($s) => [
                'wood_stock_id'  => $s->id,
                'label'          => "{$s->grade} · Ø{$s->diameter} · P{$s->panjang}",
                'volume_per_pcs' => (float) $s->volume,
                'harga'          => (float) ($s->price->harga_beli_per_m3 ?? 0),
                'qty_sistem'     => (int) ($s->quantity ?? 0),
            ])->all();
    }

    private function snapshotPalet(StockOpname $opname): array
    {
        $pcs = PalletStock::where('status', $opname->pallet_status)
            ->lockForUpdate()
            ->get()
            ->groupBy('pallet_code')
            ->map(fn ($rows) => (int) $rows->sum('pcs'));

        $hargaField = $opname->pallet_status === 'kering' ? 'harga_kering' : 'harga_basah';

        return PalletPrice::orderBy('id')->get()->map(fn ($p) => [
            'pallet_code'    => $p->pallet_code,
            'label'          => $p->ukuran,
            'volume_per_pcs' => (float) $p->volume_per_pcs,
            'harga'          => (float) ($p->{$hargaField} ?? 0),
            'qty_sistem'     => $pcs[$p->pallet_code] ?? 0,
        ])->all();
    }

    private function hitungSelisih(StockOpnameItem $item): void
    {
        if ($item->qty_fisik === null) {
            $item->selisih = $item->volume_selisih = $item->nilai_selisih = null;
            return;
        }

        $item->selisih        = $item->qty_fisik - $item->qty_sistem;
        $item->volume_selisih = round($item->selisih * $item->volume_per_pcs, 3);
        $item->nilai_selisih  = round($item->volume_selisih * $item->harga, 2);
    }

    // Stok kayu = jumlah fisik
    private function sesuaikanKayu(StockOpname $opname): void
    {
        foreach ($opname->items()->where('selisih', '!=', 0)->get() as $item) {
            $stock = WoodStock::lockForUpdate()->find($item->wood_stock_id);
            if (!$stock) {
                continue;
            }
            $stock->quantity     = $item->qty_fisik;
            $stock->total_volume = round($item->qty_fisik * $stock->volume, 3);
            $stock->save();
        }
    }

    /**
     * Stok palet: kurang -> dikurangi FIFO (stok paling lama dulu),
     * lebih -> ditambah baris stok baru berkode OPN.
     */
    private function sesuaikanPalet(StockOpname $opname, $itemSelisih): void
    {
        $status = $opname->pallet_status;
        $today  = now()->toDateString();
        $kodeInput = null; // satu kode OPN untuk semua kelebihan dalam opname ini

        foreach ($itemSelisih as $item) {
            $price = PalletPrice::where('pallet_code', $item->pallet_code)->first();
            if (!$price) {
                continue;
            }

            if ($item->selisih < 0) {
                $kurangi = -$item->selisih;
                $stocks = PalletStock::where('pallet_code', $item->pallet_code)
                    ->where('status', $status)
                    ->where('pcs', '>', 0)
                    ->orderBy('tanggal_input')->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($stocks as $stock) {
                    if ($kurangi <= 0) {
                        break;
                    }
                    $ambil = min($stock->pcs, $kurangi);
                    $volumePerPcs  = $stock->pcs > 0 ? $stock->volume / $stock->pcs : 0;
                    $stock->volume = max(0, round($stock->volume - $volumePerPcs * $ambil, 3));
                    $stock->pcs   -= $ambil;
                    $kurangi      -= $ambil;

                    $stock->pcs > 0 ? $stock->save() : $stock->delete();
                }
            } else {
                PalletStock::create([
                    'pallet_code'   => $price->pallet_code,
                    'ukuran'        => $price->ukuran,
                    'pcs'           => $item->selisih,
                    'volume'        => round($item->selisih * $price->volume_per_pcs, 3),
                    'status'        => $status,
                    'input_code'    => $kodeInput ??= $this->kodeInputOpname($today),
                    'tanggal_input' => $today,
                ]);
            }
        }
    }

    private function kodeInputOpname(string $tanggal): string
    {
        $prefix = 'OPN' . date('Ymd', strtotime($tanggal)) . '-';
        $last = PalletStock::where('input_code', 'like', $prefix . '%')->orderByDesc('input_code')->value('input_code');
        $urut = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }
}
