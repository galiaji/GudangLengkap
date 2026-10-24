<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\WoodPrice;
use App\Models\WoodType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WoodTypeController extends Controller
{
    public function index()
    {
        // Yang disembunyikan diletakkan di bawah
        $woodTypes = WoodType::withCount('stocks')
            ->withSum('stocks as total_quantity', 'quantity')
            ->orderBy('is_hidden')
            ->orderBy('name')
            ->get()
            ->each(function ($type) {
                $type->transaksi_count = $this->transaksiCount($type);
                $type->deleteBlocker   = $this->deleteBlocker($type);
            });

        return view(
            'wood_types.index',
            compact('woodTypes')
        );
    }

    /**
     * Hapus jenis kayu beserta kombinasi stok (kosong) dan daftar harganya.
     * Ditolak jika masih ada stok atau sudah punya riwayat transaksi.
     */
    public function destroy(WoodType $woodType)
    {
        if ($pesan = StockOpname::kunciKayu($woodType->id)) {
            return back()->with('error', $pesan);
        }

        $blocker = $this->deleteBlocker($woodType);
        if ($blocker) {
            return back()->with('error', "Jenis kayu {$woodType->name} tidak bisa dihapus: {$blocker}");
        }

        DB::transaction(function () use ($woodType) {
            DB::table('wood_stocks')->where('wood_type_id', $woodType->id)->delete();
            WoodPrice::where('wood_type', $woodType->name)->delete();
            $woodType->delete();
        });

        return back()->with('success', "Jenis kayu {$woodType->name} beserta kombinasi stok dan daftar harganya berhasil dihapus.");
    }

    /**
     * Sembunyikan jenis kayu dari form & dashboard (riwayat tetap ada).
     * Hanya boleh jika stoknya sudah 0.
     */
    public function hide(WoodType $woodType)
    {
        if ($pesan = StockOpname::kunciKayu($woodType->id)) {
            return back()->with('error', $pesan);
        }

        $qty = $this->totalQuantity($woodType);
        if ($qty > 0) {
            return back()->with('error', "Jenis kayu {$woodType->name} tidak bisa disembunyikan: masih ada stok {$qty} pcs.");
        }

        $woodType->update(['is_hidden' => true]);

        return back()->with('success', "Jenis kayu {$woodType->name} disembunyikan. Riwayat transaksinya tetap tersimpan, dan bisa ditampilkan kembali kapan saja.");
    }

    // Tampilkan kembali jenis kayu yang disembunyikan
    public function unhide(WoodType $woodType)
    {
        $woodType->update(['is_hidden' => false]);

        return back()->with('success', "Jenis kayu {$woodType->name} ditampilkan kembali di form dan dashboard.");
    }

    private function totalQuantity(WoodType $woodType): int
    {
        return (int) DB::table('wood_stocks')->where('wood_type_id', $woodType->id)->sum('quantity');
    }

    // Jumlah detail transaksi yang memakai jenis kayu ini
    private function transaksiCount(WoodType $woodType): int
    {
        $stockIds = DB::table('wood_stocks')->where('wood_type_id', $woodType->id)->pluck('id');

        return DB::table('wood_transaction_details')
            ->where(function ($q) use ($woodType, $stockIds) {
                $q->where('wood_type_id', $woodType->id)
                  ->orWhereIn('wood_stock_id', $stockIds);
            })
            ->count();
    }

    // Alasan jenis kayu tidak boleh dihapus, atau null jika boleh
    private function deleteBlocker(WoodType $woodType): ?string
    {
        $qty = (int) DB::table('wood_stocks')->where('wood_type_id', $woodType->id)->sum('quantity');
        if ($qty > 0) {
            return "masih ada stok {$qty} pcs.";
        }

        if ($this->transaksiCount($woodType) > 0) {
            return 'sudah memiliki riwayat transaksi.';
        }

        return null;
    }

    public function store(Request $request)
    {
        $hidden = WoodType::where('name', trim((string) $request->name))->where('is_hidden', true)->first();
        if ($hidden) {
            return back()->withInput()->withErrors([
                'name' => "Jenis kayu {$hidden->name} sudah ada tetapi sedang disembunyikan. Klik \"Tampilkan\" pada tabel di bawah untuk memakainya lagi.",
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:wood_types,name',
        ], [
            'name.required' => 'Nama jenis kayu wajib diisi.',
            'name.unique'   => 'Jenis kayu ini sudah ada.',
        ]);

        $name = trim($request->name);

        [$jumlahHarga, $jumlahStok] = DB::transaction(function () use ($name) {
            $woodType = WoodType::create(['name' => $name]);

            return $this->generateKombinasi($woodType);
        });

        // Langsung ke halaman harga supaya harga jenis kayu baru bisa diisi
        return redirect()
            ->route('wood_prices.index')
            ->with('success', "Jenis kayu {$name} berhasil ditambahkan, beserta {$jumlahHarga} daftar harga dan {$jumlahStok} kombinasi stok. Harga masih 0, silakan isi.")
            ->with('new_wood_type', $name);
    }

    /**
     * Buat daftar harga (grade x panjang) dan stok (grade x diameter x panjang)
     * untuk jenis kayu baru, dengan menyalin kombinasi yang sudah ada di database.
     *
     * @return array [jumlah harga dibuat, jumlah stok dibuat]
     */
    private function generateKombinasi(WoodType $woodType): array
    {
        // Semua kombinasi unik dari stok yang sudah ada (semua jenis kayu).
        // Volume per batang hanya bergantung pada diameter & panjang.
        $kombinasi = DB::table('wood_stocks')
            ->select('grade', 'diameter', 'panjang', DB::raw('MAX(volume) as volume'))
            ->groupBy('grade', 'diameter', 'panjang')
            ->orderBy('grade')->orderBy('diameter')->orderBy('panjang')
            ->get();

        if ($kombinasi->isEmpty()) {
            return [0, 0];
        }

        // 1. Daftar harga: satu baris per grade x panjang
        //    (tabel wood_prices masih memakai NAMA jenis kayu)
        $priceIds = [];
        foreach ($kombinasi->unique(fn ($k) => $k->grade . '|' . $k->panjang) as $k) {
            $price = WoodPrice::firstOrCreate(
                [
                    'wood_type' => $woodType->name,
                    'grade'     => $k->grade,
                    'panjang'   => $k->panjang,
                ],
                [
                    'harga_beli_per_m3' => 0,
                    'harga_jual_per_m3' => 0,
                ]
            );
            $priceIds[$k->grade . '|' . $k->panjang] = $price->id;
        }

        // 2. Daftar stok: satu baris per grade x diameter x panjang
        $now  = now();
        $rows = $kombinasi->map(fn ($k) => [
            'wood_type_id'  => $woodType->id,
            'grade'         => $k->grade,
            'diameter'      => $k->diameter,
            'panjang'       => $k->panjang,
            'volume'        => $k->volume ?? 0,
            'quantity'      => 0,
            'total_volume'  => 0,
            'wood_price_id' => $priceIds[$k->grade . '|' . $k->panjang] ?? null,
            'created_at'    => $now,
            'updated_at'    => $now,
        ])->all();

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('wood_stocks')->insert($chunk);
        }

        return [count($priceIds), count($rows)];
    }
}
