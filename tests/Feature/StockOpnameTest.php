<?php

namespace Tests\Feature;

use App\Models\PalletPrice;
use App\Models\PalletStock;
use App\Models\StockOpname;
use App\Models\User;
use App\Models\WoodPrice;
use App\Models\WoodStock;
use App\Models\WoodType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Black box test stock opname:
 * - UC-GD-20 Melakukan stock opname log kayu (SRS-19)
 * - UC-GD-21 Melakukan stock opname pallet (SRS-20)
 */
class StockOpnameTest extends TestCase
{
    use DatabaseTransactions;

    private static int $n = 0;

    private function user(string $role = 'admin'): User
    {
        $i = ++self::$n;
        return User::create([
            'name' => "SO {$role} {$i}", 'username' => "so_{$role}_{$i}", 'email' => "so_{$role}_{$i}@test.local",
            'password' => bcrypt('rahasia123'), 'role' => $role,
        ]);
    }

    /** Jenis kayu dengan dua kombinasi stok: A (Ø20, 0.041 m³/btg) dan B (Ø25, 0.064 m³/btg). */
    private function kayu(int $qtyA = 10, int $qtyB = 4, bool $hidden = false): array
    {
        $type  = WoodType::create(['name' => 'SOKayu' . (++self::$n), 'is_hidden' => $hidden]);
        $price = WoodPrice::create(['wood_type' => $type->name, 'grade' => 'Super 1', 'panjang' => 130,
            'harga_beli_per_m3' => 1000000, 'harga_jual_per_m3' => 1500000]);
        $buat = function ($diameter, $qty, $vol) use ($type, $price) {
            $s = WoodStock::create(['wood_type_id' => $type->id, 'grade' => 'Super 1', 'diameter' => $diameter,
                'panjang' => 130, 'quantity' => $qty, 'total_volume' => $qty * $vol, 'volume' => $vol]);
            $s->wood_price_id = $price->id;
            $s->save();
            return $s;
        };
        return [$type, $buat(20, $qtyA, 0.041), $buat(25, $qtyB, 0.064)];
    }

    private function palet(string $ukuran = '8x1.2x100'): PalletPrice
    {
        return PalletPrice::create(['pallet_code' => 'SO' . (++self::$n), 'ukuran' => $ukuran,
            'volume_per_pcs' => PalletPrice::volumeFromUkuran($ukuran), 'harga_basah' => 1000000, 'harga_kering' => 1500000]);
    }

    /** Satu batch berisi beberapa ukuran: [[PalletPrice, pcs], ...] */
    private function batch(array $isi, string $status = 'basah', string $tgl = '2026-10-01'): array
    {
        $kode = strtoupper(substr($status, 0, 3)) . 'SO-' . (++self::$n);
        return array_map(fn ($baris) => PalletStock::create([
            'pallet_code' => $baris[0]->pallet_code, 'ukuran' => $baris[0]->ukuran, 'pcs' => $baris[1],
            'volume' => round($baris[1] * $baris[0]->volumeM3PerPcs(), 3), 'status' => $status,
            'input_code' => $kode, 'tanggal_input' => $tgl,
        ]), $isi);
    }

    /** Data form: fisik per id stok + stok tercatat yang tampil saat halaman dibuka. */
    private function isian(array $stocks, array $fisik, string $kolomQty): array
    {
        $data = ['fisik' => [], 'sistem' => []];
        foreach ($stocks as $i => $s) {
            $data['fisik'][$s->id]  = $fisik[$i];
            $data['sistem'][$s->id] = $s->{$kolomQty};
        }
        return $data;
    }

    private function simpanKayu(User $u, WoodType $t, array $stocks, array $fisik, array $over = [])
    {
        return $this->actingAs($u)->post(route('stock-opname.kayu.store'),
            array_merge(['wood_type_id' => $t->id], $this->isian($stocks, $fisik, 'quantity'), $over));
    }

    /**
     * Data form pallet: semua ukuran terdaftar, dikunci per kode ukuran.
     * Ukuran yang tidak disebut di $fisik diisi sama dengan stok tercatat (tidak ada selisih).
     */
    private function isianPalet(string $inputCode, array $fisik): array
    {
        $pcs  = PalletStock::where('input_code', $inputCode)->pluck('pcs', 'pallet_code');
        $data = ['fisik' => [], 'sistem' => []];
        foreach (PalletPrice::pluck('pallet_code') as $kode) {
            $data['sistem'][$kode] = (int) ($pcs[$kode] ?? 0);
            $data['fisik'][$kode]  = array_key_exists($kode, $fisik) ? $fisik[$kode] : $data['sistem'][$kode];
        }
        return $data;
    }

    private function simpanPalet(User $u, string $inputCode, array $fisik, array $over = [])
    {
        return $this->actingAs($u)->post(route('stock-opname.palet.store'),
            array_merge(['input_code' => $inputCode], $this->isianPalet($inputCode, $fisik), $over));
    }

    // ================= UC-GD-20: Stock opname log kayu =================

    public function test_K01_menu_dan_pilih_jenis_kayu_menampilkan_stok_tercatat(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $u = $this->user();

        $this->actingAs($u)->get(route('stock-opname.kayu'))->assertOk()
            ->assertSee('Stock Opname Log Kayu')->assertSee($t->name)->assertDontSee('name="fisik[', false);

        $this->actingAs($u)->get(route('stock-opname.kayu', ['wood_type_id' => $t->id]))->assertOk()
            ->assertViewHas('stocks', fn ($s) => $s->pluck('id')->all() === [$a->id, $b->id])
            ->assertSee('Stock Opname Log Kayu - ' . $t->name)->assertSee('Tercatat: 10')->assertSee('Total Fisik')
            ->assertSee('name="fisik[' . $a->id . ']"', false)
            ->assertSee('Selisih Volume');
    }

    public function test_K02_simpan_dengan_selisih_menyesuaikan_stok(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $u = $this->user();

        $res = $this->simpanKayu($u, $t, [$a, $b], [7, 6], ['keterangan' => 'Hitung ulang gudang A']);

        $o = StockOpname::latest('id')->first();
        $res->assertRedirect(route('stock-opname.show', $o))->assertSessionHas('success');
        $this->assertSame('kayu', $o->jenis);
        $this->assertSame('Hitung ulang gudang A', $o->catatan);
        $this->assertSame($u->id, $o->dibuat_oleh);

        // Selisih jumlah & volume tersimpan
        $ia = $o->items()->where('wood_stock_id', $a->id)->first();
        $this->assertSame([10, 7, -3], [$ia->qty_sistem, $ia->qty_fisik, $ia->selisih]);
        $this->assertEqualsWithDelta(-0.123, $ia->volume_selisih, 0.0001); // -3 x 0,041
        $ib = $o->items()->where('wood_stock_id', $b->id)->first();
        $this->assertEqualsWithDelta(0.128, $ib->volume_selisih, 0.0001);  // +2 x 0,064

        // Stok log kayu disesuaikan dengan hasil hitung fisik
        $this->assertEquals(7, $a->fresh()->quantity);
        $this->assertEqualsWithDelta(0.287, (float) $a->fresh()->total_volume, 0.0001);
        $this->assertEquals(6, $b->fresh()->quantity);

        $this->actingAs($u)->get(route('stock-opname.show', $o))->assertOk()
            ->assertSee($o->kode)->assertSee('Hitung ulang gudang A')->assertSee('-0,123');
    }

    public function test_K03_tanpa_selisih_tetap_disimpan_tanpa_mengubah_stok(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $sebelum = [$a->updated_at, $b->updated_at];

        $this->simpanKayu($this->user(), $t, [$a, $b], [10, 4])
            ->assertSessionHas('success', fn ($p) => str_contains($p, 'Tidak ada selisih'));

        $o = StockOpname::latest('id')->first();
        $this->assertEquals(2, $o->items()->count());
        $this->assertEquals(0, $o->items()->where('selisih', '!=', 0)->count());
        $this->assertEquals(10, $a->fresh()->quantity);
        $this->assertEquals($sebelum, [$a->fresh()->updated_at, $b->fresh()->updated_at]);
    }

    public function test_K04_isian_kosong_memunculkan_pesan_validasi(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $jumlah = StockOpname::count();

        $this->simpanKayu($this->user(), $t, [$a, $b], [7, ''])
            ->assertSessionHasErrors(["fisik.{$b->id}" => 'Jumlah fisik wajib diisi untuk semua baris.']);

        $this->assertEquals($jumlah, StockOpname::count());
        $this->assertEquals(10, $a->fresh()->quantity);
    }

    public function test_K05_isian_bukan_angka_desimal_atau_negatif_ditolak(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $u = $this->user();

        $this->simpanKayu($u, $t, [$a, $b], ['abc', 2.5])
            ->assertSessionHasErrors(["fisik.{$a->id}" => 'Jumlah fisik harus berupa angka bulat.', "fisik.{$b->id}"]);
        $this->simpanKayu($u, $t, [$a, $b], [-1, 4])
            ->assertSessionHasErrors(["fisik.{$a->id}" => 'Jumlah fisik tidak boleh negatif.']);

        $this->assertEquals(10, $a->fresh()->quantity);
    }

    public function test_K06_baris_yang_tidak_dikirim_ditolak(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);

        $this->simpanKayu($this->user(), $t, [$a], [7])
            ->assertSessionHasErrors(['fisik' => 'Masih ada 1 baris yang belum diisi. Isi 0 jika barangnya tidak ada.']);
        $this->assertEquals(10, $a->fresh()->quantity);
    }

    public function test_K07_stok_berubah_sejak_halaman_dibuka_ditolak(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $form = $this->isian([$a, $b], [7, 4], 'quantity');
        $a->update(['quantity' => 12]); // ada transaksi pembelian di antaranya

        $this->actingAs($this->user())->post(route('stock-opname.kayu.store'), ['wood_type_id' => $t->id] + $form)
            ->assertSessionHasErrors('fisik');
        $this->assertEquals(12, $a->fresh()->quantity);
        $this->assertEquals(0, StockOpname::where('wood_type_id', $t->id)->count());
    }

    public function test_K08_jenis_kayu_kosong_atau_disembunyikan_ditolak(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4, true);
        $u = $this->user();

        $this->simpanKayu($u, $t, [$a, $b], [7, 4], ['wood_type_id' => ''])
            ->assertSessionHasErrors(['wood_type_id' => 'Jenis kayu wajib dipilih.']);
        $this->simpanKayu($u, $t, [$a, $b], [7, 4])->assertSessionHasErrors('wood_type_id');
        $this->assertEquals(10, $a->fresh()->quantity);
    }

    // ================= UC-GD-21: Stock opname pallet per batch =================

    public function test_P01_menu_dan_pilih_batch_menampilkan_semua_ukuran_beserta_status(): void
    {
        $p1 = $this->palet('8x1.2x100');
        $p2 = $this->palet('10x1.2x130');
        $p3 = $this->palet('12x1.2x100');  // terdaftar tapi belum ada di batch
        [$r1, $r2] = $this->batch([[$p1, 20], [$p2, 15]], 'penjemuran');
        $u = $this->user();

        $this->actingAs($u)->get(route('stock-opname.palet'))->assertOk()
            ->assertSee('Stock Opname Pallet')->assertSee($r1->input_code);

        $this->actingAs($u)->get(route('stock-opname.palet', ['input_code' => $r1->input_code]))->assertOk()
            ->assertViewHas('baris', function ($baris) use ($p1, $p2, $p3) {
                $b = $baris->keyBy('kode');
                return $baris->count() === PalletPrice::count()
                    && $b[$p1->pallet_code]->sistem === 20 && $b[$p2->pallet_code]->sistem === 15
                    && $b[$p3->pallet_code]->sistem === 0 && $b[$p3->pallet_code]->row === null
                    && $b[$p3->pallet_code]->status === 'penjemuran';
            })
            ->assertSee('Stok Tercatat Batch ' . $r1->input_code)
            ->assertSee('8x1.2x100')->assertSee('12x1.2x100')->assertSee('Penjemuran')
            ->assertSee('belum ada di batch')
            ->assertSee('name="fisik[' . $p3->pallet_code . ']"', false);
    }

    public function test_P01b_ukuran_yang_belum_terinput_ditambahkan_ke_batch(): void
    {
        $p1 = $this->palet('8x1.2x100');
        $p3 = $this->palet('12x1.2x100');  // 0.00144 m³/pcs, belum ada di batch
        [$r1] = $this->batch([[$p1, 20]], 'penjemuran', '2026-10-08');

        $this->simpanPalet($this->user(), $r1->input_code, [$p3->pallet_code => 6])->assertSessionHas('success');

        // Ditambahkan ke batch yang sama: kode batch, status, dan tanggal ikut batch
        $baru = PalletStock::where('input_code', $r1->input_code)->where('pallet_code', $p3->pallet_code)->first();
        $this->assertNotNull($baru);
        $this->assertSame([6, '12x1.2x100', 'penjemuran', '2026-10-08'],
            [(int) $baru->pcs, $baru->ukuran, $baru->status, (string) $baru->tanggal_input]);
        $this->assertEqualsWithDelta(0.009, (float) $baru->volume, 0.0001); // 6 x 0,00144 = 0,00864
        $this->assertEquals(20, $r1->fresh()->pcs);

        $item = StockOpname::latest('id')->first()->items()->where('pallet_code', $p3->pallet_code)->first();
        $this->assertSame([0, 6, 6], [$item->qty_sistem, $item->qty_fisik, $item->selisih]);
    }

    public function test_P02_simpan_menyesuaikan_hanya_batch_yang_dipilih(): void
    {
        $p1 = $this->palet('8x1.2x100');   // 0.00096 m³/pcs
        $p2 = $this->palet('10x1.2x130');  // 0.00156 m³/pcs
        [$lain] = $this->batch([[$p1, 50]], 'kering', '2026-09-01');
        [$r1, $r2] = $this->batch([[$p1, 20], [$p2, 15]], 'kering');
        $u = $this->user();

        // p1 kurang 5, p2 habis (0)
        $this->simpanPalet($u, $r1->input_code, [$p1->pallet_code => 15, $p2->pallet_code => 0], ['keterangan' => 'Ada yang patah'])
            ->assertSessionHas('success');

        $o = StockOpname::latest('id')->first();
        $this->assertSame(['palet', 'kering', $r1->input_code, 'Ada yang patah'], [$o->jenis, $o->pallet_status, $o->input_code, $o->catatan]);
        $i1 = $o->items()->where('pallet_code', $p1->pallet_code)->first();
        $this->assertSame([20, 15, -5], [$i1->qty_sistem, $i1->qty_fisik, $i1->selisih]);
        $this->assertEqualsWithDelta(-0.005, $i1->volume_selisih, 0.0001);     // -5 x 0,00096 = -0,0048
        $this->assertEqualsWithDelta(-7200, $i1->nilai_selisih, 0.01);         // x Rp 1.500.000/m³ (harga kering)

        // Batch terpilih disesuaikan; ukuran yang fisiknya 0 dihapus dari batch
        $this->assertEquals(15, $r1->fresh()->pcs);
        $this->assertEqualsWithDelta(0.014, (float) $r1->fresh()->volume, 0.0001); // 15 x 0,00096 = 0,0144
        $this->assertDatabaseMissing('pallet_stocks', ['id' => $r2->id]);

        // Batch lain dengan ukuran sama tidak tersentuh (bukan FIFO)
        $this->assertEquals(50, $lain->fresh()->pcs);
    }

    public function test_P03_fisik_lebih_banyak_menambah_stok_batch(): void
    {
        $p = $this->palet('10x1.2x130');
        [$r] = $this->batch([[$p, 10]], 'basah');

        $this->simpanPalet($this->user(), $r->input_code, [$p->pallet_code => 14])->assertSessionHas('success');

        $this->assertEquals(14, $r->fresh()->pcs);
        $this->assertEqualsWithDelta(0.022, (float) $r->fresh()->volume, 0.0001); // 14 x 0,00156 = 0,02184
        $item = StockOpname::latest('id')->first()->items()->where('pallet_code', $p->pallet_code)->first();
        $this->assertEquals(4, $item->selisih);
        $this->assertEqualsWithDelta(6240, $item->nilai_selisih, 0.01); // 4 x 0,00156 x Rp 1.000.000 (harga basah)
    }

    public function test_P04_tanpa_selisih_tetap_disimpan_tanpa_mengubah_stok(): void
    {
        $p = $this->palet();
        [$r] = $this->batch([[$p, 10]]);
        $sebelum = $r->updated_at;

        $this->simpanPalet($this->user(), $r->input_code, [])
            ->assertSessionHas('success', fn ($p) => str_contains($p, 'Tidak ada selisih'));
        $this->assertEquals(1, PalletStock::where('input_code', $r->input_code)->count()); // tidak ada ukuran baru
        $this->assertEquals(1, StockOpname::where('input_code', $r->input_code)->count());
        $this->assertEquals($sebelum, $r->fresh()->updated_at);
    }

    public function test_P05_isian_kosong_atau_bukan_angka_ditolak(): void
    {
        $p1 = $this->palet();
        $p2 = $this->palet('12x1.2x100');
        [$r1, $r2] = $this->batch([[$p1, 10], [$p2, 8]]);
        $u = $this->user();

        $this->simpanPalet($u, $r1->input_code, [$p1->pallet_code => '', $p2->pallet_code => 'x'])->assertSessionHasErrors([
            "fisik.{$p1->pallet_code}" => 'Jumlah fisik wajib diisi untuk semua baris.',
            "fisik.{$p2->pallet_code}" => 'Jumlah fisik harus berupa angka bulat.',
        ]);

        // Ada ukuran yang tidak dikirim
        $form = $this->isianPalet($r1->input_code, []);
        unset($form['fisik'][$p2->pallet_code]);
        $this->actingAs($u)->post(route('stock-opname.palet.store'), ['input_code' => $r1->input_code] + $form)
            ->assertSessionHasErrors('fisik');

        $this->assertEquals(0, StockOpname::where('input_code', $r1->input_code)->count());
        $this->assertEquals(10, $r1->fresh()->pcs);
    }

    public function test_P06_batch_kosong_atau_tidak_ada_ditolak(): void
    {
        $p = $this->palet();
        [$r] = $this->batch([[$p, 10]]);
        $u = $this->user();

        $this->simpanPalet($u, $r->input_code, [$p->pallet_code => 9], ['input_code' => ''])
            ->assertSessionHasErrors(['input_code' => 'Batch wajib dipilih.']);
        $this->simpanPalet($u, $r->input_code, [$p->pallet_code => 9], ['input_code' => 'TIDAKADA-001'])
            ->assertSessionHasErrors(['input_code' => 'Batch tidak ditemukan atau stoknya sudah habis.']);
        $this->assertEquals(10, $r->fresh()->pcs);
    }

    public function test_P07_stok_batch_berubah_sejak_halaman_dibuka_ditolak(): void
    {
        $p = $this->palet();
        [$r] = $this->batch([[$p, 10]]);
        $form = $this->isianPalet($r->input_code, [$p->pallet_code => 9]);
        $r->update(['pcs' => 6]); // ada penjualan di antaranya

        $this->actingAs($this->user())->post(route('stock-opname.palet.store'), ['input_code' => $r->input_code] + $form)
            ->assertSessionHasErrors('fisik');
        $this->assertEquals(6, $r->fresh()->pcs);
    }

    // ================= Umum =================

    public function test_U01_riwayat_tampil_dan_detail_hasil_bisa_dibuka(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $p = $this->palet();
        [$r] = $this->batch([[$p, 10]]);
        $u = $this->user();
        $this->simpanKayu($u, $t, [$a, $b], [9, 4]);
        $ok = StockOpname::latest('id')->first();
        $this->simpanPalet($u, $r->input_code, []);
        $op = StockOpname::latest('id')->first();

        // Satu halaman riwayat, dua tabel bersebelahan: log kayu & pallet
        $this->actingAs($u)->get(route('stock-opname.index'))->assertOk()
            ->assertSee('Riwayat Stock Opname Log Kayu')->assertSee('Riwayat Stock Opname Pallet')
            ->assertSee($ok->kode)->assertSee($op->kode)
            ->assertSee(route('stock-opname.kayu'), false)->assertSee(route('stock-opname.palet'), false)
            ->assertViewHas('kayu', fn ($r) => $r->contains($ok) && !$r->contains($op))
            ->assertViewHas('palet', fn ($r) => $r->contains($op) && !$r->contains($ok));
        $this->actingAs($u)->get(route('stock-opname.show', $op))->assertOk()->assertSee('Batch ' . $r->input_code);
        $this->actingAs($u)->get('/stock-opname/999999')->assertNotFound();
    }

    public function test_U02_hanya_admin_yang_bisa_akses(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $p = $this->palet();
        [$r] = $this->batch([[$p, 10]]);
        $this->simpanKayu($this->user('admin'), $t, [$a, $b], [10, 4]);
        $o = StockOpname::latest('id')->first();

        $this->actingAs($this->user('admin'))->get(route('stock-opname.index'))->assertOk();
        $this->actingAs($this->user('admin'))->get(route('stock-opname.palet'))->assertOk();

        // Staff ditolak di semua halaman & aksi stock opname, stok tidak berubah
        $staff = $this->user('staff');
        $this->actingAs($staff)->get(route('stock-opname.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('stock-opname.kayu'))->assertForbidden();
        $this->actingAs($staff)->get(route('stock-opname.palet'))->assertForbidden();
        $this->actingAs($staff)->get(route('stock-opname.show', $o))->assertForbidden();
        $this->simpanKayu($staff, $t, [$a, $b], [1, 1])->assertForbidden();
        $this->simpanPalet($staff, $r->input_code, [$p->pallet_code => 1])->assertForbidden();
        $this->assertEquals(10, $a->fresh()->quantity);
        $this->assertEquals(10, $r->fresh()->pcs);

        auth()->logout();
        $this->get(route('stock-opname.palet'))->assertRedirect(route('login'));
    }

    public function test_U03_menu_stock_opname_hanya_tampil_untuk_admin(): void
    {
        // Satu menu Stock Opname menuju halaman riwayat
        $this->actingAs($this->user('admin'))->get(route('dashboard'))
            ->assertSee('href="' . route('stock-opname.index') . '"', false)
            ->assertDontSee('href="' . route('stock-opname.kayu') . '"', false);

        $this->actingAs($this->user('staff'))->get(route('dashboard'))
            ->assertDontSee('href="' . route('stock-opname.index') . '"', false);
    }
}
