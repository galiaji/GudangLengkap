<?php

namespace Tests\Feature;

use App\Models\PalletPrice;
use App\Models\PalletSale;
use App\Models\PalletSaleItem;
use App\Models\PalletStock;
use App\Models\User;
use App\Models\WoodPrice;
use App\Models\WoodStock;
use App\Models\WoodTransaction;
use App\Models\WoodTransactionDetail;
use App\Models\WoodType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

/**
 * Black box testing putaran 2: kasus batas, baris ganda,
 * nilai yang dimanipulasi dari browser, dan hak akses.
 */
class BlackBoxRound2Test extends TestCase
{
    use DatabaseTransactions;

    private static int $n = 0;

    private function user(string $role = 'admin'): User
    {
        $i = ++self::$n;
        return User::create([
            'name' => "R2 {$role} {$i}", 'username' => "r2_{$role}_{$i}", 'email' => "r2_{$role}_{$i}@test.local",
            'password' => bcrypt('rahasia123'), 'role' => $role,
        ]);
    }

    private function kayu(int $qty = 10, bool $hidden = false): array
    {
        $type  = WoodType::create(['name' => 'R2Kayu' . (++self::$n), 'is_hidden' => $hidden]);
        $price = WoodPrice::create([
            'wood_type' => $type->name, 'grade' => 'Super 1', 'panjang' => 130,
            'harga_beli_per_m3' => 1000000, 'harga_jual_per_m3' => 1500000,
        ]);
        $stock = WoodStock::create([
            'wood_type_id' => $type->id, 'grade' => 'Super 1', 'diameter' => 20, 'panjang' => 130,
            'quantity' => $qty, 'total_volume' => $qty * 0.041, 'volume' => 0.041,
        ]);
        $stock->wood_price_id = $price->id;
        $stock->save();
        return [$type, $stock, $price];
    }

    private function baris(array $over = []): array
    {
        return array_merge(['grade' => 'Super 1', 'diameter' => 20, 'panjang' => 130, 'pcs' => 1, 'volume' => 0.041, 'harga' => 41000], $over);
    }

    private function trx(WoodType $t, array $rows, array $over = []): array
    {
        return array_merge(['atas_nama' => 'Bu Sari', 'wood_type_id' => $t->id, 'total_volume' => 0, 'total_pembayaran' => 0, 'details' => $rows], $over);
    }

    private function palet(string $ukuran = '8x1.2x100', float $basah = 1000, float $kering = 1500): PalletPrice
    {
        return PalletPrice::create([
            'pallet_code' => 'R' . (++self::$n), 'ukuran' => $ukuran,
            'volume_per_pcs' => PalletPrice::volumeFromUkuran($ukuran), 'harga_basah' => $basah, 'harga_kering' => $kering,
        ]);
    }

    private function stokPalet(PalletPrice $p, int $pcs, string $status = 'basah', string $tgl = '2026-10-01'): PalletStock
    {
        return PalletStock::create([
            'pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => $pcs, 'volume' => round($pcs * $p->volumeM3PerPcs(), 3),
            'status' => $status, 'input_code' => 'R2' . (++self::$n), 'tanggal_input' => $tgl,
        ]);
    }

    private function itemPalet(PalletPrice $p, int $pcs, string $status = 'basah', array $over = []): array
    {
        $harga = $status === 'basah' ? $p->harga_basah : $p->harga_kering;
        $vol   = $pcs * $p->volumeM3PerPcs();
        return array_merge([
            'ukuran' => $p->ukuran, 'pcs' => $pcs, 'volume' => $vol, 'harga_satuan' => $harga,
            'subtotal' => number_format($vol * $harga, 0, ',', '.'),
        ], $over);
    }

    // ========== R-A. Baris ganda dalam satu transaksi kayu ==========

    public function test_RA1_jual_kayu_dua_baris_sama_total_melebihi_stok(): void
    {
        [$t, $s] = $this->kayu(5);
        $res = $this->actingAs($this->user())->postJson(route('wood_transactions.storeSell'),
            $this->trx($t, [$this->baris(['pcs' => 3]), $this->baris(['pcs' => 3])]));
        $this->assertEquals(5, $s->fresh()->quantity, 'Terjual 6 pcs padahal stok 5; stok tersisa ' . $s->fresh()->quantity);
        $res->assertStatus(422);
    }

    public function test_RA2_beli_kayu_dua_baris_sama_stok_bertambah_penuh(): void
    {
        [$t, $s] = $this->kayu(10);
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeBuy'),
            $this->trx($t, [$this->baris(['pcs' => 2]), $this->baris(['pcs' => 3])]))->assertOk();
        $this->assertEquals(15, $s->fresh()->quantity, 'Seharusnya 10 + 2 + 3 = 15');
    }

    public function test_RA3_potong_gergaji_dua_baris_sama_melebihi_stok(): void
    {
        [$t, $s] = $this->kayu(4);
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeSaw'),
            $this->trx($t, [$this->baris(['pcs' => 3]), $this->baris(['pcs' => 3])], ['jenis_transaksi' => 'gergajian']));
        $this->assertEquals(4, $s->fresh()->quantity, 'Dipotong 6 pcs padahal stok 4; stok tersisa ' . $s->fresh()->quantity);
    }

    // ========== R-B. Kasus batas transaksi kayu ==========

    public function test_RB1_jual_kayu_tepat_seluruh_stok(): void
    {
        [$t, $s] = $this->kayu(5);
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeSell'),
            $this->trx($t, [$this->baris(['pcs' => 5])]))->assertOk();
        $this->assertEquals(0, $s->fresh()->quantity);
    }

    public function test_RB2_jual_kayu_pcs_desimal(): void
    {
        [$t] = $this->kayu(5);
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeSell'),
            $this->trx($t, [$this->baris(['pcs' => 1.5])]))->assertStatus(422)->assertJsonValidationErrors('details.0.pcs');
    }

    public function test_RB3_jual_kayu_tanpa_rincian(): void
    {
        [$t] = $this->kayu(5);
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeSell'), $this->trx($t, []))
            ->assertStatus(422)->assertJsonValidationErrors('details');
    }

    public function test_RB4_beli_kayu_jenis_tidak_ada(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeBuy'),
            $this->trx($t, [$this->baris()], ['wood_type_id' => 999999]))->assertStatus(422)->assertJsonValidationErrors('wood_type_id');
    }

    public function test_RB5_total_tersimpan_sesuai_rincian_multi_baris(): void
    {
        [$t] = $this->kayu(10);
        $url = $this->actingAs($this->user())->postJson(route('wood_transactions.storeSell'),
            $this->trx($t, [$this->baris(['pcs' => 1, 'harga' => 61500]), $this->baris(['pcs' => 2, 'volume' => 0.082, 'harga' => 123000])],
                ['total_pembayaran' => 5]))->assertOk()->json('redirect_url');
        $trx = WoodTransaction::latest('id')->first();
        $this->assertEquals(184500, (float) $trx->total_pembayaran);
        $this->assertEqualsWithDelta(0.123, (float) $trx->total_volume, 0.0001);
    }

    public function test_RB6_nama_berisi_script_ditampilkan_aman(): void
    {
        [$t] = $this->kayu(10);
        $u = $this->user();
        $this->actingAs($u)->postJson(route('wood_transactions.storeBuy'),
            $this->trx($t, [$this->baris()], ['atas_nama' => '<script>alert(1)</script>']))->assertOk();
        $this->actingAs($u)->get(route('transaksi.index'))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }

    // ========== R-B2. Harga kayu selalu per m³, bukan dari browser ==========

    public function test_RB7_beli_kayu_harga_dan_volume_dimanipulasi(): void
    {
        [$t, $s] = $this->kayu(10); // 0.041 m³/batang, beli Rp 1.000.000/m³
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeBuy'),
            $this->trx($t, [$this->baris(['pcs' => 2, 'volume' => 5, 'harga' => 1])]))->assertOk();
        $d = WoodTransactionDetail::latest('id')->first();
        $this->assertEqualsWithDelta(0.082, (float) $d->volume, 0.0001, "Volume tersimpan {$d->volume}, seharusnya 2 x 0,041");
        $this->assertEquals(82000, (float) $d->harga, "Harga tersimpan {$d->harga}, seharusnya 0,082 m³ x Rp 1.000.000");
        $this->assertEquals(82000, (float) WoodTransaction::latest('id')->first()->total_pembayaran);
        $this->assertEqualsWithDelta(0.492, (float) $s->fresh()->total_volume, 0.0001, 'Stok bertambah 0,082 m³, bukan 5');
    }

    public function test_RB8_jual_kayu_harga_dimanipulasi(): void
    {
        [$t] = $this->kayu(10); // jual Rp 1.500.000/m³
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeSell'),
            $this->trx($t, [$this->baris(['pcs' => 2, 'volume' => 0.082, 'harga' => 1])]))->assertOk();
        $this->assertEquals(123000, (float) WoodTransactionDetail::latest('id')->first()->harga, 'Seharusnya 0,082 m³ x Rp 1.500.000');
    }

    public function test_RB9_potong_gergaji_harga_dihitung_per_m3(): void
    {
        [$t] = $this->kayu(10);
        $this->actingAs($this->user())->postJson(route('wood_transactions.storeSaw'),
            $this->trx($t, [$this->baris(['pcs' => 3, 'volume' => 0.123, 'harga' => 999999999])], ['jenis_transaksi' => 'gergajian']))->assertOk();
        $trx = WoodTransaction::latest('id')->first();
        $this->assertEquals(184500, (float) $trx->total_pembayaran, 'Seharusnya 0,123 m³ x Rp 1.500.000');
        $this->assertEqualsWithDelta(0.123, (float) $trx->total_volume, 0.0001);
    }

    // ========== R-C. Nilai palet yang dikirim browser ==========

    public function test_RC1_jual_palet_subtotal_dimanipulasi(): void
    {
        $p = $this->palet('8x1.2x100', 1000000); // 0.00096 m³/pcs x Rp 1.000.000/m³ = 960 per pcs
        $this->stokPalet($p, 10);
        $this->actingAs($this->user())->post(route('pallet-sales.store'), [
            'tanggal' => '2026-10-24', 'customer' => 'R2 Manipulasi',
            'basah' => [$this->itemPalet($p, 5, 'basah', ['subtotal' => '1'])],
        ]);
        $item = PalletSaleItem::latest('id')->first();
        $this->assertEquals(4800, (float) $item->subtotal, "Subtotal tersimpan {$item->subtotal}, seharusnya 5 x 0,00096 x 1.000.000 = 4800");
    }

    public function test_RC2_jual_palet_volume_dimanipulasi(): void
    {
        $p = $this->palet();
        $this->stokPalet($p, 10);
        $this->actingAs($this->user())->post(route('pallet-sales.store'), [
            'tanggal' => '2026-10-24', 'customer' => 'R2 Volume',
            'basah' => [$this->itemPalet($p, 5, 'basah', ['volume' => 999])],
        ]);
        $item = PalletSaleItem::latest('id')->first();
        // 5 x 0,00096 = 0,0048 m³ -> disimpan 3 desimal = 0,005
        $this->assertEqualsWithDelta(0.005, (float) $item->volume, 0.0001, "Volume tersimpan {$item->volume}, seharusnya 0,005 m³");
    }

    public function test_RC3_input_stok_palet_volume_dimanipulasi(): void
    {
        $p = $this->palet();
        $this->actingAs($this->user())->post(route('pallet_stocks.store'), [
            'tanggal_input' => '2026-10-21', 'status' => 'basah',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => 10, 'volume' => 999]],
        ]);
        $s = PalletStock::where('pallet_code', $p->pallet_code)->first();
        // 10 x 0,00096 = 0,0096 m³ -> disimpan 3 desimal = 0,010
        $this->assertEqualsWithDelta(0.010, (float) $s->volume, 0.0001, "Volume tersimpan {$s->volume}, seharusnya 0,010 m³");
    }

    public function test_RC4_input_stok_palet_ukuran_tidak_cocok_dengan_kode(): void
    {
        $p = $this->palet('8x1.2x100');
        $this->actingAs($this->user())->post(route('pallet_stocks.store'), [
            'tanggal_input' => '2026-10-21', 'status' => 'basah',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => '99x99x99', 'pcs' => 10, 'volume' => 9.6]],
        ]);
        $this->assertDatabaseMissing('pallet_stocks', ['pallet_code' => $p->pallet_code, 'ukuran' => '99x99x99']);
    }

    // ========== R-D. Penjualan & stok palet lainnya ==========

    public function test_RD1_jual_palet_kering_fifo(): void
    {
        $p = $this->palet();
        $lama = $this->stokPalet($p, 4, 'kering', '2026-09-01');
        $baru = $this->stokPalet($p, 10, 'kering', '2026-10-01');
        $this->actingAs($this->user())->post(route('pallet-sales.store'), [
            'tanggal' => '2026-10-24', 'customer' => 'R2 Kering', 'kering' => [$this->itemPalet($p, 6, 'kering')],
        ])->assertRedirect(route('pallet-sales.index'));
        $this->assertDatabaseMissing('pallet_stocks', ['id' => $lama->id]);
        $this->assertEquals(8, $baru->fresh()->pcs);
    }

    public function test_RD2_jual_palet_basah_dan_kering_sekaligus(): void
    {
        $p = $this->palet();
        $b = $this->stokPalet($p, 10, 'basah');
        $k = $this->stokPalet($p, 10, 'kering');
        $this->actingAs($this->user())->post(route('pallet-sales.store'), [
            'tanggal' => '2026-10-24', 'customer' => 'R2 Campur',
            'basah' => [$this->itemPalet($p, 2, 'basah')], 'kering' => [$this->itemPalet($p, 3, 'kering')],
        ])->assertRedirect(route('pallet-sales.index'));
        $this->assertEquals(8, $b->fresh()->pcs);
        $this->assertEquals(7, $k->fresh()->pcs);
        $this->assertEquals(2, PalletSale::where('customer', 'R2 Campur')->first()->items()->count());
    }

    public function test_RD3_jual_palet_kering_stok_kurang_basah_tidak_ikut_berkurang(): void
    {
        $p = $this->palet();
        $b = $this->stokPalet($p, 10, 'basah');
        $this->stokPalet($p, 1, 'kering');
        $this->actingAs($this->user())->post(route('pallet-sales.store'), [
            'tanggal' => '2026-10-24', 'customer' => 'R2 Gagal',
            'basah' => [$this->itemPalet($p, 2, 'basah')], 'kering' => [$this->itemPalet($p, 5, 'kering')],
        ])->assertSessionHas('error');
        $this->assertEquals(10, $b->fresh()->pcs, 'Transaksi gagal tapi stok basah tetap berkurang');
        $this->assertDatabaseMissing('pallet_sales', ['customer' => 'R2 Gagal']);
    }

    public function test_RD4_jual_palet_ukuran_tidak_terdaftar(): void
    {
        $p = $this->palet();
        $this->actingAs($this->user())->post(route('pallet-sales.store'), [
            'tanggal' => '2026-10-24', 'customer' => 'R2 Asing',
            'basah' => [$this->itemPalet($p, 2, 'basah', ['ukuran' => '1x1x1'])],
        ])->assertSessionHas('error');
        $this->assertDatabaseMissing('pallet_sales', ['customer' => 'R2 Asing']);
    }

    public function test_RD5_jual_palet_pcs_negatif(): void
    {
        $p = $this->palet();
        $s = $this->stokPalet($p, 10);
        $this->actingAs($this->user())->post(route('pallet-sales.store'), [
            'tanggal' => '2026-10-24', 'customer' => 'R2 Negatif', 'basah' => [$this->itemPalet($p, -5)],
        ]);
        $this->assertEquals(10, $s->fresh()->pcs);
        $this->assertDatabaseMissing('pallet_sales', ['customer' => 'R2 Negatif']);
    }

    public function test_RD6_jual_palet_tanggal_kosong(): void
    {
        $p = $this->palet();
        $this->stokPalet($p, 10);
        $this->actingAs($this->user())->post(route('pallet-sales.store'), [
            'tanggal' => '', 'customer' => 'R2', 'basah' => [$this->itemPalet($p, 1)],
        ])->assertSessionHasErrors('tanggal');
    }

    public function test_RD7_input_stok_palet_pcs_negatif(): void
    {
        $p = $this->palet();
        $this->actingAs($this->user())->post(route('pallet_stocks.store'), [
            'tanggal_input' => '2026-10-21',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => -3, 'volume' => 0]],
        ])->assertSessionHasErrors('pallets.0.pcs');
    }

    public function test_RD8_input_stok_palet_penjemuran_dan_nomor_urut(): void
    {
        $p = $this->palet();
        $u = $this->user();
        $kirim = fn () => $this->actingAs($u)->post(route('pallet_stocks.store'), [
            'tanggal_input' => '2026-10-21', 'status' => 'penjemuran',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => 3, 'volume' => 2.88]],
        ]);
        $kirim();
        $kirim();
        $this->assertDatabaseHas('pallet_stocks', ['input_code' => 'JMR20261021-001', 'status' => 'penjemuran']);
        $this->assertDatabaseHas('pallet_stocks', ['input_code' => 'JMR20261021-002', 'status' => 'penjemuran']);
    }

    public function test_RD9_pindah_status_kode_tidak_ada(): void
    {
        $this->actingAs($this->user())->post(route('stok_palet.moveToNextStatus', 'TIDAKADA'))->assertSessionHas('error');
    }

    public function test_RD10_ubah_harga_palet_id_tidak_ada(): void
    {
        $res = $this->actingAs($this->user())->put(route('pallet-prices.update', 999999), ['harga_basah' => '1000', 'harga_kering' => '1000']);
        $this->assertLessThan(500, $res->status());
        $res->assertSessionHas('error');
    }

    public function test_RD11_kode_ukuran_palet_berurutan(): void
    {
        PalletPrice::create(['pallet_code' => 'A7', 'ukuran' => '5x5x5', 'harga_basah' => 0, 'harga_kering' => 0]);
        $this->actingAs($this->user())->post(route('pallet-prices.store'), ['tebal' => 6, 'lebar' => 6, 'panjang' => 6]);
        $this->assertDatabaseHas('pallet_prices', ['ukuran' => '6x6x6', 'pallet_code' => 'A8']);
    }

    public function test_RD12_cari_penjualan_palet_per_customer(): void
    {
        $p = $this->palet();
        $this->stokPalet($p, 10);
        $u = $this->user();
        $this->actingAs($u)->post(route('pallet-sales.store'), ['tanggal' => '2026-10-24', 'customer' => 'Toko Cari', 'basah' => [$this->itemPalet($p, 1)]]);
        $this->actingAs($u)->get(route('pallet-sales.index', ['customer' => 'Cari']))->assertOk()->assertSee('Toko Cari');
    }

    // ========== R-E. Hak akses staff ==========

    public function test_RE1_staff_tidak_bisa_membuka_menu_admin(): void
    {
        $s = $this->user('staff');
        foreach ([route('pallet-prices.index'), route('pallet-prices.create'), route('wood-types.index'), route('stok.range.create'), route('account.create')] as $url) {
            $this->actingAs($s)->get($url)->assertForbidden();
        }
    }

    public function test_RE2_staff_tidak_bisa_mengubah_data_admin(): void
    {
        $s = $this->user('staff');
        [$t, , $price] = $this->kayu();
        $p = $this->palet();
        $this->actingAs($s)->post(route('wood_prices.updateBeli', Hashids::encode($price->id)), ['harga_beli_per_m3' => 1])->assertForbidden();
        $this->actingAs($s)->put(route('pallet-prices.update', $p->id), ['harga_basah' => '1', 'harga_kering' => '1'])->assertForbidden();
        $this->actingAs($s)->post(route('wood-types.store'), ['name' => 'Ilegal'])->assertForbidden();
        $this->actingAs($s)->delete(route('wood-types.destroy', $t))->assertForbidden();
        $this->actingAs($s)->post(route('account.store'), [])->assertForbidden();
        $this->assertEquals(1000000, $price->fresh()->harga_beli_per_m3);
    }

    public function test_RE3_role_user_bisa_login_dan_membuka_dashboard(): void
    {
        $this->actingAs($this->user('user'))->get('/dashboard')->assertOk();
    }

    // ========== R-F. Akun ==========

    public function test_RF1_edit_akun_email_duplikat(): void
    {
        $a = $this->user();
        $b = $this->user('staff');
        $this->actingAs($a)->put(route('account.update', Hashids::encode($b->id)), [
            'name' => $b->name, 'username' => $b->username, 'email' => $a->email, 'role' => 'staff',
        ])->assertSessionHasErrors('email');
    }

    public function test_RF2_tambah_akun_role_tidak_valid(): void
    {
        $this->actingAs($this->user())->post(route('account.store'), [
            'name' => 'X', 'username' => 'r2super', 'email' => 'r2super@test.local',
            'role' => 'superadmin', 'password' => 'abcdef', 'password_confirmation' => 'abcdef',
        ])->assertSessionHasErrors('role');
    }

    public function test_RF3_ganti_password_lalu_login_dengan_password_baru(): void
    {
        $a = $this->user();
        $s = $this->user('staff');
        $this->actingAs($a)->put(route('account.update', Hashids::encode($s->id)), [
            'name' => $s->name, 'username' => $s->username, 'email' => $s->email, 'role' => 'staff',
            'password' => 'baru1234', 'password_confirmation' => 'baru1234',
        ]);
        auth()->logout();
        $this->post('/login', ['username' => $s->username, 'password' => 'baru1234'])->assertRedirect('/dashboard');
    }

    // ========== R-G. Jenis kayu ==========

    public function test_RG1_tambah_jenis_kayu_yang_sedang_disembunyikan(): void
    {
        [$t] = $this->kayu(0, true);
        $this->actingAs($this->user())->post(route('wood-types.store'), ['name' => $t->name])->assertSessionHasErrors('name');
    }

    public function test_RG2_hapus_jenis_kayu_dengan_riwayat_transaksi(): void
    {
        [$t] = $this->kayu(5);
        $u = $this->user();
        $this->actingAs($u)->postJson(route('wood_transactions.storeSell'), $this->trx($t, [$this->baris(['pcs' => 5])]))->assertOk();
        $this->actingAs($u)->delete(route('wood-types.destroy', $t))->assertSessionHas('error');
        $this->assertDatabaseHas('wood_types', ['id' => $t->id]);
    }

    public function test_RG3_jenis_kayu_disembunyikan_tidak_muncul_di_form(): void
    {
        [$t] = $this->kayu(0, true);
        $this->actingAs($this->user())->get(route('wood_transactions.buy'))->assertOk()->assertViewHas('woodTypes', fn ($w) => !$w->contains('id', $t->id));
    }

    public function test_RG4_tambah_jenis_kayu_nama_berspasi_duplikat(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->user())->post(route('wood-types.store'), ['name' => '  ' . $t->name . '  ']);
        $this->assertEquals(1, WoodType::where('name', $t->name)->count());
    }

    // ========== R-H. Laporan, detail & export ==========

    public function test_RH1_laporan_harian_menghitung_transaksi_hari_ini(): void
    {
        [$t] = $this->kayu(10);
        $u = $this->user();
        $this->actingAs($u)->postJson(route('wood_transactions.storeBuy'), $this->trx($t, [$this->baris(['harga' => 41000])]))->assertOk();
        // Laporan langsung terunduh sebagai PDF
        $this->actingAs($u)->get(route('transaksi.laporan.harian'))->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload();
    }

    public function test_RH2_laporan_tahunan_tahun_tidak_valid(): void
    {
        $res = $this->actingAs($this->user())->get(route('transaksi.laporan.tahunan', ['tahun' => 'abc']));
        $this->assertLessThan(500, $res->status());
    }

    public function test_RH3_detail_transaksi_gergajian(): void
    {
        [$t] = $this->kayu(5);
        $u = $this->user();
        $url = $this->actingAs($u)->postJson(route('wood_transactions.storeSaw'),
            $this->trx($t, [$this->baris()], ['jenis_transaksi' => 'gergajian']))->assertOk()->json('redirect_url');
        $this->actingAs($u)->get($url)->assertOk()->assertSee('Bu Sari');
    }

    public function test_RH4_filter_transaksi_per_tanggal(): void
    {
        [$t] = $this->kayu(10);
        $u = $this->user();
        $this->actingAs($u)->postJson(route('wood_transactions.storeBuy'), $this->trx($t, [$this->baris()], ['atas_nama' => 'Hari Ini R2']));
        $this->actingAs($u)->get(route('transaksi.index', ['tanggal' => now()->toDateString()]))->assertSee('Hari Ini R2');
        $this->actingAs($u)->get(route('transaksi.index', ['tanggal' => '2000-01-01']))->assertDontSee('Hari Ini R2');
    }

    public function test_RH5_export_stok_pdf_dengan_nama_dan_tidak_ada(): void
    {
        [$t] = $this->kayu();
        $u = $this->user();
        $this->actingAs($u)->get(route('export.stok.pdf', $t->name))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($u)->get(route('export.stok.pdf', 'TidakAdaKayu'))->assertNotFound();
    }

    public function test_RH6_dashboard_dengan_data_lengkap(): void
    {
        [$t] = $this->kayu(10);
        $p = $this->palet();
        $this->stokPalet($p, 5);
        $this->stokPalet($p, 5, 'kering');
        $this->actingAs($this->user('staff'))->get('/dashboard')->assertOk();
    }
}
