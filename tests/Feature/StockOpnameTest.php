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

    /** Jenis kayu dengan dua kombinasi stok: A (qty $qtyA) dan B (qty $qtyB). */
    private function kayu(int $qtyA = 10, int $qtyB = 4): array
    {
        $type  = WoodType::create(['name' => 'SOKayu' . (++self::$n)]);
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
            'volume_per_pcs' => PalletPrice::volumeFromUkuran($ukuran), 'harga_basah' => 1000, 'harga_kering' => 1500]);
    }

    private function stokPalet(PalletPrice $p, int $pcs, string $status = 'basah', string $tgl = '2026-10-01'): PalletStock
    {
        return PalletStock::create(['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => $pcs,
            'volume' => $pcs * $p->volume_per_pcs, 'status' => $status, 'input_code' => 'SOI' . (++self::$n), 'tanggal_input' => $tgl]);
    }

    private function mulaiKayu(User $u, WoodType $t): StockOpname
    {
        $this->actingAs($u)->post(route('stock-opname.store'), ['jenis' => 'kayu', 'wood_type_id' => $t->id])
            ->assertRedirect();
        return StockOpname::where('wood_type_id', $t->id)->latest('id')->firstOrFail();
    }

    private function mulaiPalet(User $u, string $status): StockOpname
    {
        $this->actingAs($u)->post(route('stock-opname.store'), ['jenis' => 'palet', 'pallet_status' => $status])
            ->assertRedirect();
        return StockOpname::where('pallet_status', $status)->latest('id')->firstOrFail();
    }

    /** Isi jumlah fisik berdasarkan label atau pallet_code => qty, lalu ajukan. */
    private function hitungDanAjukan(User $u, StockOpname $o, callable $fisikDari)
    {
        $fisik = [];
        foreach ($o->items as $item) {
            $fisik[$item->id] = $fisikDari($item);
        }
        return $this->actingAs($u)->post(route('stock-opname.hitung', $o), ['fisik' => $fisik, 'aksi' => 'ajukan']);
    }

    private function trxKayu(WoodType $t, int $pcs = 1): array
    {
        return ['atas_nama' => 'Uji', 'wood_type_id' => $t->id, 'details' => [[
            'grade' => 'Super 1', 'diameter' => 20, 'panjang' => 130, 'pcs' => $pcs, 'volume' => 0.041 * $pcs, 'harga' => 41000 * $pcs,
        ]]];
    }

    // ========== Halaman ==========

    public function test_SO01_halaman_daftar_dan_form_tampil(): void
    {
        $u = $this->user('staff');
        $this->actingAs($u)->get(route('stock-opname.index'))->assertOk();
        $this->actingAs($u)->get(route('stock-opname.create'))->assertOk();
    }

    // ========== Mulai & snapshot ==========

    public function test_SO02_mulai_opname_kayu_menyimpan_snapshot(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $o = $this->mulaiKayu($this->user('staff'), $t);

        $this->assertSame('menghitung', $o->status);
        $this->assertSame('Kayu ' . $t->name, $o->cakupan);
        $this->assertMatchesRegularExpression('/^SO-\d{8}-\d{2}$/', $o->kode);
        $this->assertEquals(2, $o->items()->count());
        $this->assertEquals(10, $o->items()->where('wood_stock_id', $a->id)->value('qty_sistem'));
        $this->assertEquals(1000000, $o->items()->where('wood_stock_id', $a->id)->value('harga'));
    }

    public function test_SO03_mulai_opname_palet_menyimpan_semua_ukuran(): void
    {
        $p1 = $this->palet('8x1.2x100');
        $p2 = $this->palet('10x1.2x130');
        $this->stokPalet($p1, 5);
        $this->stokPalet($p1, 7);
        $this->stokPalet($p1, 99, 'kering'); // status lain tidak ikut
        $o = $this->mulaiPalet($this->user(), 'basah');

        $this->assertEquals(12, $o->items()->where('pallet_code', $p1->pallet_code)->value('qty_sistem'));
        $this->assertEquals(0, $o->items()->where('pallet_code', $p2->pallet_code)->value('qty_sistem'));
    }

    public function test_SO04_tidak_bisa_opname_ganda_untuk_stok_yang_sama(): void
    {
        [$t] = $this->kayu();
        $u = $this->user();
        $this->mulaiKayu($u, $t);
        $this->actingAs($u)->post(route('stock-opname.store'), ['jenis' => 'kayu', 'wood_type_id' => $t->id])
            ->assertSessionHas('error');
        $this->assertEquals(1, StockOpname::where('wood_type_id', $t->id)->count());
    }

    public function test_SO05_validasi_mulai_opname(): void
    {
        $u = $this->user();
        $this->actingAs($u)->post(route('stock-opname.store'), ['jenis' => 'kayu'])->assertSessionHasErrors('wood_type_id');
        $this->actingAs($u)->post(route('stock-opname.store'), ['jenis' => 'palet', 'pallet_status' => 'ngawur'])->assertSessionHasErrors('pallet_status');
        $this->actingAs($u)->post(route('stock-opname.store'), ['jenis' => 'lain'])->assertSessionHasErrors('jenis');
    }

    // ========== Penguncian ==========

    public function test_SO06_kunci_kayu_menolak_semua_transaksi_jenis_itu(): void
    {
        [$t, $a] = $this->kayu(10);
        $u = $this->user();
        $this->mulaiKayu($u, $t);

        $this->actingAs($u)->postJson(route('wood_transactions.storeBuy'), $this->trxKayu($t))
            ->assertStatus(422)->assertJsonFragment(['success' => false]);
        $this->actingAs($u)->postJson(route('wood_transactions.storeSell'), $this->trxKayu($t))->assertStatus(422);
        $this->actingAs($u)->postJson(route('wood_transactions.storeSaw'), $this->trxKayu($t) + ['jenis_transaksi' => 'gergajian'])->assertStatus(422);
        $this->actingAs($u)->post(route('stok.range.store'), ['wood_type_id' => $t->id, 'grade' => 'Super 2', 'diameter' => 30, 'panjang' => 200])
            ->assertSessionHasErrors('wood_type_id');
        $this->actingAs($u)->patch(route('wood-types.hide', $t))->assertSessionHas('error');
        $this->actingAs($u)->delete(route('wood-types.destroy', $t))->assertSessionHas('error');

        $this->assertEquals(10, $a->fresh()->quantity);
    }

    public function test_SO07_kunci_kayu_tidak_mengganggu_jenis_kayu_lain(): void
    {
        [$t1] = $this->kayu();
        [$t2, $a2] = $this->kayu(10);
        $u = $this->user();
        $this->mulaiKayu($u, $t1);

        $this->actingAs($u)->postJson(route('wood_transactions.storeSell'), $this->trxKayu($t2, 2))->assertOk();
        $this->assertEquals(8, $a2->fresh()->quantity);
    }

    public function test_SO08_kunci_palet_menolak_transaksi_status_itu(): void
    {
        $p = $this->palet();
        $s = $this->stokPalet($p, 10, 'basah');
        $k = $this->stokPalet($p, 10, 'kering');
        $u = $this->user();
        $this->mulaiPalet($u, 'basah');

        // input stok basah ditolak, kering tetap boleh
        $this->actingAs($u)->post(route('pallet_stocks.store'), ['tanggal_input' => '2026-10-24', 'status' => 'basah',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => 5, 'volume' => 4.8]]])
            ->assertSessionHas('error');
        $this->actingAs($u)->post(route('pallet_stocks.store'), ['tanggal_input' => '2026-10-24', 'status' => 'kering',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => 5, 'volume' => 4.8]]])
            ->assertRedirect(route('pallet_stocks.index'));

        // pindah status dari basah ditolak
        $this->actingAs($u)->post(route('stok_palet.moveToNextStatus', $s->input_code))->assertSessionHas('error');
        $this->assertSame('basah', $s->fresh()->status);

        // jual basah ditolak, jual kering boleh
        $item = fn ($pcs) => [['ukuran' => $p->ukuran, 'pcs' => $pcs, 'volume' => 0, 'harga_satuan' => 0, 'subtotal' => '0']];
        $this->actingAs($u)->post(route('pallet-sales.store'), ['tanggal' => '2026-10-24', 'customer' => 'X', 'basah' => $item(2)])
            ->assertSessionHas('error');
        $this->assertEquals(10, $s->fresh()->pcs);
        $this->actingAs($u)->post(route('pallet-sales.store'), ['tanggal' => '2026-10-24', 'customer' => 'Y', 'kering' => $item(2)])
            ->assertRedirect(route('pallet-sales.index'));
        $this->assertEquals(8, $k->fresh()->pcs);
    }

    public function test_SO09_pindah_status_ke_status_yang_dikunci_ditolak(): void
    {
        $p = $this->palet();
        $s = $this->stokPalet($p, 10, 'basah');
        $u = $this->user();
        $this->mulaiPalet($u, 'penjemuran');
        $this->actingAs($u)->post(route('stok_palet.moveToNextStatus', $s->input_code))->assertSessionHas('error');
        $this->assertSame('basah', $s->fresh()->status);
    }

    public function test_SO10_banner_kunci_tampil_di_halaman(): void
    {
        [$t] = $this->kayu();
        $u = $this->user('staff');
        $o = $this->mulaiKayu($u, $t);
        $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee('Stock opname sedang berlangsung')->assertSee($o->kode);
    }

    // ========== Menghitung ==========

    public function test_SO11_form_hitung_tidak_menampilkan_stok_sistem(): void
    {
        [$t] = $this->kayu(987654, 4);
        $u = $this->user('staff');
        $o = $this->mulaiKayu($u, $t);
        $this->actingAs($u)->get(route('stock-opname.show', $o))->assertOk()
            ->assertSee('Input Hasil Hitung Fisik')->assertDontSee('987654')->assertDontSee('987.654');
    }

    public function test_SO12_simpan_sementara_lalu_ajukan_wajib_lengkap(): void
    {
        [$t, $a] = $this->kayu();
        $u = $this->user('staff');
        $o = $this->mulaiKayu($u, $t);
        $itemA = $o->items()->where('wood_stock_id', $a->id)->first();

        $this->actingAs($u)->post(route('stock-opname.hitung', $o), ['fisik' => [$itemA->id => 9], 'aksi' => 'simpan'])
            ->assertSessionHas('success');
        $this->assertEquals(9, $itemA->fresh()->qty_fisik);
        $this->assertSame('menghitung', $o->fresh()->status);

        $this->actingAs($u)->post(route('stock-opname.hitung', $o), ['fisik' => [$itemA->id => 9], 'aksi' => 'ajukan'])
            ->assertSessionHas('error');
        $this->assertSame('menghitung', $o->fresh()->status);
    }

    public function test_SO13_jumlah_fisik_negatif_atau_desimal_ditolak(): void
    {
        [$t] = $this->kayu();
        $u = $this->user('staff');
        $o = $this->mulaiKayu($u, $t);
        $id = $o->items()->first()->id;
        $this->actingAs($u)->post(route('stock-opname.hitung', $o), ['fisik' => [$id => -1], 'aksi' => 'simpan'])
            ->assertSessionHasErrors("fisik.{$id}");
        $this->actingAs($u)->post(route('stock-opname.hitung', $o), ['fisik' => [$id => 1.5], 'aksi' => 'simpan'])
            ->assertSessionHasErrors("fisik.{$id}");
    }

    public function test_SO14_ajukan_menghitung_selisih(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $u = $this->user('staff');
        $o = $this->mulaiKayu($u, $t);
        $this->hitungDanAjukan($u, $o, fn ($i) => $i->wood_stock_id === $a->id ? 7 : 6)->assertRedirect();

        $o->refresh();
        $this->assertSame('menunggu', $o->status);
        $ia = $o->items()->where('wood_stock_id', $a->id)->first();
        $this->assertEquals(-3, $ia->selisih);
        $this->assertEqualsWithDelta(-0.123, $ia->volume_selisih, 0.0001);
        $this->assertEqualsWithDelta(-123000, $ia->nilai_selisih, 0.01);
        $this->assertEquals(2, $o->items()->where('wood_stock_id', $b->id)->value('selisih'));
    }

    public function test_SO15_hasil_hitung_tidak_bisa_diubah_setelah_diajukan(): void
    {
        [$t, $a] = $this->kayu(10, 4);
        $u = $this->user('staff');
        $o = $this->mulaiKayu($u, $t);
        $this->hitungDanAjukan($u, $o, fn ($i) => 5);
        $id = $o->items()->first()->id;
        $this->actingAs($u)->post(route('stock-opname.hitung', $o), ['fisik' => [$id => 100], 'aksi' => 'simpan'])
            ->assertSessionHas('error');
        $this->assertEquals(5, $o->items()->first()->qty_fisik);
    }

    // ========== Persetujuan ==========

    public function test_SO16_staff_tidak_bisa_menyetujui_mengembalikan_membatalkan(): void
    {
        [$t] = $this->kayu();
        $s = $this->user('staff');
        $o = $this->mulaiKayu($s, $t);
        $this->hitungDanAjukan($s, $o, fn ($i) => 0);
        $this->actingAs($s)->post(route('stock-opname.setujui', $o))->assertForbidden();
        $this->actingAs($s)->post(route('stock-opname.kembalikan', $o))->assertForbidden();
        $this->actingAs($s)->post(route('stock-opname.batal', $o))->assertForbidden();
        $this->assertSame('menunggu', $o->fresh()->status);
    }

    public function test_SO17_setujui_wajib_alasan_untuk_item_selisih(): void
    {
        [$t, $a] = $this->kayu(10, 4);
        $admin = $this->user();
        $o = $this->mulaiKayu($admin, $t);
        $this->hitungDanAjukan($admin, $o, fn ($i) => $i->wood_stock_id === $a->id ? 7 : 4);
        $ia = $o->items()->where('wood_stock_id', $a->id)->first();

        $this->actingAs($admin)->post(route('stock-opname.setujui', $o), [])->assertSessionHasErrors("alasan.{$ia->id}");
        $this->actingAs($admin)->post(route('stock-opname.setujui', $o), ['alasan' => [$ia->id => 'Ngawur']])->assertSessionHasErrors("alasan.{$ia->id}");
        $this->assertSame('menunggu', $o->fresh()->status);
        $this->assertEquals(10, $a->fresh()->quantity);
    }

    public function test_SO18_setujui_kayu_menyesuaikan_stok_dan_melepas_kunci(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $admin = $this->user();
        $o = $this->mulaiKayu($admin, $t);
        $this->hitungDanAjukan($admin, $o, fn ($i) => $i->wood_stock_id === $a->id ? 7 : 6);
        $ia = $o->items()->where('wood_stock_id', $a->id)->first();
        $ib = $o->items()->where('wood_stock_id', $b->id)->first();

        $this->actingAs($admin)->post(route('stock-opname.setujui', $o), [
            'alasan' => [$ia->id => 'Hilang', $ib->id => 'Salah catat'],
            'keterangan' => [$ia->id => 'Diduga terbawa truk'],
        ])->assertSessionHas('success');

        $o->refresh();
        $this->assertSame('selesai', $o->status);
        $this->assertEquals($admin->id, $o->disetujui_oleh);
        $this->assertNotNull($o->disetujui_at);
        $this->assertEquals(7, $a->fresh()->quantity);
        $this->assertEqualsWithDelta(0.287, (float) $a->fresh()->total_volume, 0.0001);
        $this->assertEquals(6, $b->fresh()->quantity);
        $this->assertSame('Hilang', $ia->fresh()->alasan);
        $this->assertSame('Diduga terbawa truk', $ia->fresh()->keterangan);

        // kunci dilepas: transaksi bisa lagi
        $this->actingAs($admin)->postJson(route('wood_transactions.storeSell'), $this->trxKayu($t, 2))->assertOk();
        $this->assertEquals(5, $a->fresh()->quantity);
    }

    public function test_SO19_setujui_tanpa_selisih_tidak_mengubah_stok(): void
    {
        [$t, $a, $b] = $this->kayu(10, 4);
        $admin = $this->user();
        $o = $this->mulaiKayu($admin, $t);
        $this->hitungDanAjukan($admin, $o, fn ($i) => $i->qty_sistem);
        $this->actingAs($admin)->post(route('stock-opname.setujui', $o))->assertSessionHas('success');
        $this->assertSame('selesai', $o->fresh()->status);
        $this->assertEquals(10, $a->fresh()->quantity);
        $this->assertEquals(4, $b->fresh()->quantity);
    }

    public function test_SO20_setujui_palet_kurang_fifo_dan_lebih_baris_opn(): void
    {
        $p1 = $this->palet('8x1.2x100');   // 0.96 / pcs
        $p2 = $this->palet('10x1.2x130');  // 1.56 / pcs
        $lama = $this->stokPalet($p1, 3, 'basah', '2026-09-01');
        $baru = $this->stokPalet($p1, 10, 'basah', '2026-10-01');
        $admin = $this->user();
        $o = $this->mulaiPalet($admin, 'basah');

        // p1: sistem 13, fisik 8 (kurang 5); p2: sistem 0, fisik 4 (lebih 4)
        $this->hitungDanAjukan($admin, $o, fn ($i) => $i->pallet_code === $p1->pallet_code ? 8 : ($i->pallet_code === $p2->pallet_code ? 4 : $i->qty_sistem));
        $alasan = $o->items()->where('selisih', '!=', 0)->pluck('id')->mapWithKeys(fn ($id) => [$id => 'Salah catat'])->all();
        $this->actingAs($admin)->post(route('stock-opname.setujui', $o), ['alasan' => $alasan])->assertSessionHas('success');

        // FIFO: stok lama (3) habis, stok baru 10 -> 8, volume ikut turun
        $this->assertDatabaseMissing('pallet_stocks', ['id' => $lama->id]);
        $this->assertEquals(8, $baru->fresh()->pcs);
        $this->assertEqualsWithDelta(7.68, (float) $baru->fresh()->volume, 0.001);

        // kelebihan dicatat sebagai baris baru berkode OPN
        $opn = PalletStock::where('pallet_code', $p2->pallet_code)->where('status', 'basah')->first();
        $this->assertNotNull($opn);
        $this->assertStringStartsWith('OPN', $opn->input_code);
        $this->assertEquals(4, $opn->pcs);
        $this->assertEqualsWithDelta(6.24, (float) $opn->volume, 0.001);
    }

    public function test_SO21_kembalikan_untuk_hitung_ulang(): void
    {
        [$t] = $this->kayu();
        $admin = $this->user();
        $o = $this->mulaiKayu($admin, $t);
        $this->hitungDanAjukan($admin, $o, fn ($i) => 1);
        $this->actingAs($admin)->post(route('stock-opname.kembalikan', $o))->assertSessionHas('success');
        $this->assertSame('menghitung', $o->fresh()->status);
        $this->assertNotNull(StockOpname::kunciKayu($t->id), 'Stok harus tetap terkunci');
    }

    public function test_SO22_batal_melepas_kunci_tanpa_mengubah_stok(): void
    {
        [$t, $a] = $this->kayu(10);
        $admin = $this->user();
        $o = $this->mulaiKayu($admin, $t);
        $this->hitungDanAjukan($admin, $o, fn ($i) => 0);
        $this->actingAs($admin)->post(route('stock-opname.batal', $o))->assertSessionHas('success');

        $this->assertSame('batal', $o->fresh()->status);
        $this->assertEquals(10, $a->fresh()->quantity);
        $this->assertNull(StockOpname::kunciKayu($t->id));
        // setelah batal tidak bisa disetujui
        $this->actingAs($admin)->post(route('stock-opname.setujui', $o))->assertSessionHas('error');
    }

    public function test_SO23_opname_baru_bisa_dimulai_setelah_selesai(): void
    {
        [$t] = $this->kayu();
        $admin = $this->user();
        $o = $this->mulaiKayu($admin, $t);
        $this->hitungDanAjukan($admin, $o, fn ($i) => $i->qty_sistem);
        $this->actingAs($admin)->post(route('stock-opname.setujui', $o));
        $o2 = $this->mulaiKayu($admin, $t);
        $this->assertNotEquals($o->id, $o2->id);
        $this->assertNotEquals($o->kode, $o2->kode);
    }

    // ========== Halaman hasil & PDF ==========

    public function test_SO24_halaman_pemeriksaan_dan_hasil(): void
    {
        [$t, $a] = $this->kayu(10, 4);
        $admin = $this->user();
        $staff = $this->user('staff');
        $o = $this->mulaiKayu($staff, $t);
        $this->hitungDanAjukan($staff, $o, fn ($i) => $i->wood_stock_id === $a->id ? 7 : 4);

        $this->actingAs($admin)->get(route('stock-opname.show', $o))->assertOk()->assertSee('Setujui');
        $this->actingAs($staff)->get(route('stock-opname.show', $o))->assertOk()
            ->assertSee('menunggu pemeriksaan admin')->assertDontSee('Setujui &amp; Sesuaikan', false);
        $this->actingAs($staff)->get(route('stock-opname.index'))->assertOk()->assertSee($o->kode);
    }

    public function test_SO25_lembar_hitung_dan_laporan_pdf(): void
    {
        [$t, $a] = $this->kayu(10, 4);
        $admin = $this->user();
        $o = $this->mulaiKayu($admin, $t);
        $this->actingAs($admin)->get(route('stock-opname.lembar', $o))->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->hitungDanAjukan($admin, $o, fn ($i) => $i->wood_stock_id === $a->id ? 7 : 4);
        $ia = $o->items()->where('wood_stock_id', $a->id)->first();
        $this->actingAs($admin)->post(route('stock-opname.setujui', $o), ['alasan' => [$ia->id => 'Susut']]);
        $this->actingAs($admin)->get(route('stock-opname.laporan', $o))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_SO26_opname_tidak_ada(): void
    {
        $this->actingAs($this->user())->get(route('stock-opname.show', 999999))->assertNotFound();
    }
}
