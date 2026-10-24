<?php

namespace Tests\Feature;

use App\Models\PalletPrice;
use App\Models\PalletSale;
use App\Models\PalletStock;
use App\Models\User;
use App\Models\WoodPrice;
use App\Models\WoodStock;
use App\Models\WoodTransaction;
use App\Models\WoodType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

/**
 * Black box testing: setiap test mengirim input seperti pengguna
 * lalu mengecek keluaran sistem terhadap hasil yang diharapkan.
 * Semua perubahan data dibatalkan (rollback) setelah tiap test.
 */
class BlackBoxTest extends TestCase
{
    use DatabaseTransactions;

    private static int $n = 0;

    // ---------- Data uji ----------

    private function user(string $role = 'admin', string $password = 'rahasia123'): User
    {
        $i = ++self::$n;
        return User::create([
            'name'     => "Uji {$role} {$i}",
            'username' => "uji_{$role}_{$i}",
            'email'    => "uji_{$role}_{$i}@test.local",
            'password' => bcrypt($password),
            'role'     => $role,
        ]);
    }

    private function admin(): User
    {
        return $this->user('admin');
    }

    /** Jenis kayu + 1 kombinasi stok (Super 1, D20, P130) dengan qty tertentu. */
    private function kayu(int $qty = 10, bool $hidden = false): array
    {
        $type  = WoodType::create(['name' => 'KayuUji' . (++self::$n), 'is_hidden' => $hidden]);
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

    private function transaksiKayu(WoodType $type, array $detail = [], array $over = []): array
    {
        return array_merge([
            'atas_nama'        => 'Pak Budi',
            'wood_type_id'     => $type->id,
            'total_volume'     => 0.082,
            'total_pembayaran' => 82000,
            'details'          => [array_merge([
                'grade' => 'Super 1', 'diameter' => 20, 'panjang' => 130,
                'pcs' => 2, 'volume' => 0.082, 'harga' => 82000,
            ], $detail)],
        ], $over);
    }

    private function palet(string $ukuran = '8x1.2x100'): PalletPrice
    {
        return PalletPrice::create([
            'pallet_code' => 'T' . (++self::$n), 'ukuran' => $ukuran,
            'volume_per_pcs' => PalletPrice::volumeFromUkuran($ukuran),
            'harga_basah' => 1000, 'harga_kering' => 1500,
        ]);
    }

    private function stokPalet(PalletPrice $p, int $pcs, string $status = 'basah', string $tgl = '2026-10-01'): PalletStock
    {
        return PalletStock::create([
            'pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => $pcs,
            'volume' => $pcs * $p->volume_per_pcs, 'status' => $status,
            'input_code' => 'UJI' . (++self::$n), 'tanggal_input' => $tgl,
        ]);
    }

    // ========== A. Login & hak akses ==========

    public function test_A01_login_dengan_data_benar(): void
    {
        $u = $this->user('admin', 'rahasia123');
        $this->post('/login', ['username' => $u->username, 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($u);
    }

    public function test_A02_login_password_salah(): void
    {
        $u = $this->user();
        $this->post('/login', ['username' => $u->username, 'password' => 'salah'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_A03_login_isian_kosong(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }

    public function test_A04_akses_halaman_tanpa_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_A05_staff_membuka_menu_admin(): void
    {
        $staff = $this->user('staff');
        $this->actingAs($staff)->get(route('account.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('wood_prices.index'))->assertForbidden();
    }

    public function test_A06_logout(): void
    {
        $this->actingAs($this->admin())->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
    }

    // ========== B. Dashboard & profil ==========

    public function test_B01_dashboard_tampil(): void
    {
        $this->kayu();
        $this->actingAs($this->admin())->get('/dashboard')->assertOk();
    }

    public function test_B02_profil_tampil(): void
    {
        $this->actingAs($this->user('staff'))->get(route('account.profile'))->assertOk();
    }

    // ========== C. Manajemen akun ==========

    public function test_C01_tambah_akun_valid(): void
    {
        $this->actingAs($this->admin())->post(route('account.store'), [
            'name' => 'Staff Baru', 'username' => 'staffbaru', 'email' => 'staffbaru@test.local',
            'role' => 'staff', 'password' => 'abcdef', 'password_confirmation' => 'abcdef',
        ])->assertRedirect(route('account.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['username' => 'staffbaru', 'role' => 'staff']);
    }

    public function test_C02_tambah_akun_username_duplikat(): void
    {
        $a = $this->admin();
        $this->actingAs($a)->post(route('account.store'), [
            'name' => 'X', 'username' => $a->username, 'email' => 'lain@test.local',
            'role' => 'staff', 'password' => 'abcdef', 'password_confirmation' => 'abcdef',
        ])->assertSessionHasErrors('username');
    }

    public function test_C03_tambah_akun_konfirmasi_password_beda(): void
    {
        $this->actingAs($this->admin())->post(route('account.store'), [
            'name' => 'X', 'username' => 'xbaru', 'email' => 'xbaru@test.local',
            'role' => 'staff', 'password' => 'abcdef', 'password_confirmation' => 'beda12',
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['username' => 'xbaru']);
    }

    public function test_C04_edit_akun_tanpa_ganti_password(): void
    {
        $a = $this->admin();
        $s = $this->user('staff');
        $oldHash = $s->password;
        $this->actingAs($a)->put(route('account.update', Hashids::encode($s->id)), [
            'name' => 'Nama Diubah', 'username' => $s->username, 'email' => $s->email, 'role' => 'staff',
        ])->assertRedirect(route('account.index'));
        $s->refresh();
        $this->assertSame('Nama Diubah', $s->name);
        $this->assertSame($oldHash, $s->password);
    }

    public function test_C05_hapus_akun_lain(): void
    {
        $s = $this->user('staff');
        $this->actingAs($this->admin())->delete(route('account.destroy', Hashids::encode($s->id)))
            ->assertRedirect(route('account.index'));
        $this->assertDatabaseMissing('users', ['id' => $s->id]);
    }

    public function test_C06_admin_menghapus_akunnya_sendiri_ditolak(): void
    {
        $a = $this->admin();
        $this->actingAs($a)->delete(route('account.destroy', Hashids::encode($a->id)));
        $this->assertDatabaseHas('users', ['id' => $a->id]);
    }

    public function test_C07_edit_akun_dengan_id_tidak_valid(): void
    {
        $this->actingAs($this->admin())->get(route('account.edit', 'xxxxxx'))->assertNotFound();
    }

    // ========== D. Jenis kayu ==========

    public function test_D01_tambah_jenis_kayu_valid(): void
    {
        $this->kayu(); // supaya ada kombinasi yang disalin
        $this->actingAs($this->admin())->post(route('wood-types.store'), ['name' => 'Akasia Uji'])
            ->assertRedirect(route('wood_prices.index'))->assertSessionHas('success');
        $t = WoodType::where('name', 'Akasia Uji')->first();
        $this->assertNotNull($t);
        $this->assertGreaterThan(0, WoodStock::where('wood_type_id', $t->id)->count());
    }

    public function test_D02_tambah_jenis_kayu_nama_kosong(): void
    {
        $this->actingAs($this->admin())->post(route('wood-types.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_D03_tambah_jenis_kayu_duplikat(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())->post(route('wood-types.store'), ['name' => $t->name])
            ->assertSessionHasErrors('name');
    }

    public function test_D04_sembunyikan_jenis_kayu_yang_masih_ada_stok(): void
    {
        [$t] = $this->kayu(5);
        $this->actingAs($this->admin())->patch(route('wood-types.hide', $t))->assertSessionHas('error');
        $this->assertFalse($t->fresh()->is_hidden);
    }

    public function test_D05_sembunyikan_dan_tampilkan_jenis_kayu_stok_0(): void
    {
        [$t] = $this->kayu(0);
        $a = $this->admin();
        $this->actingAs($a)->patch(route('wood-types.hide', $t))->assertSessionHas('success');
        $this->assertTrue($t->fresh()->is_hidden);
        $this->actingAs($a)->patch(route('wood-types.unhide', $t))->assertSessionHas('success');
        $this->assertFalse($t->fresh()->is_hidden);
    }

    public function test_D06_hapus_jenis_kayu_yang_masih_ada_stok(): void
    {
        [$t] = $this->kayu(5);
        $this->actingAs($this->admin())->delete(route('wood-types.destroy', $t))->assertSessionHas('error');
        $this->assertDatabaseHas('wood_types', ['id' => $t->id]);
    }

    public function test_D07_hapus_jenis_kayu_tanpa_stok_dan_transaksi(): void
    {
        [$t] = $this->kayu(0);
        $this->actingAs($this->admin())->delete(route('wood-types.destroy', $t))->assertSessionHas('success');
        $this->assertDatabaseMissing('wood_types', ['id' => $t->id]);
    }

    // ========== E. Harga kayu ==========

    public function test_E01_halaman_harga_kayu_tampil(): void
    {
        $this->kayu();
        $this->actingAs($this->admin())->get(route('wood_prices.index'))->assertOk();
    }

    public function test_E02_ubah_harga_beli_valid(): void
    {
        [, , $p] = $this->kayu();
        $this->actingAs($this->admin())
            ->post(route('wood_prices.updateBeli', Hashids::encode($p->id)), ['harga_beli_per_m3' => 2000000])
            ->assertSessionHas('success');
        $this->assertEquals(2000000, $p->fresh()->harga_beli_per_m3);
    }

    public function test_E03_ubah_harga_jual_negatif(): void
    {
        [, , $p] = $this->kayu();
        $this->actingAs($this->admin())
            ->post(route('wood_prices.updateJual', Hashids::encode($p->id)), ['harga_jual_per_m3' => -5])
            ->assertSessionHasErrors('harga_jual_per_m3');
        $this->assertEquals(1500000, $p->fresh()->harga_jual_per_m3);
    }

    public function test_E04_ubah_harga_id_tidak_valid(): void
    {
        $this->actingAs($this->admin())
            ->post(route('wood_prices.updateBeli', 'xxxxxx'), ['harga_beli_per_m3' => 1])
            ->assertSessionHas('error');
    }

    // ========== F. Tambah kombinasi stok ==========

    public function test_F01_form_kombinasi_stok_tampil(): void
    {
        $this->actingAs($this->admin())->get(route('stok.range.create'))->assertOk();
    }

    public function test_F02_tambah_kombinasi_valid(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())->post(route('stok.range.store'), [
            'wood_type_id' => $t->id, 'grade' => 'Super 2', 'diameter' => 30, 'panjang' => 200,
        ])->assertRedirect(route('stock.detail', $t->id));
        $this->assertDatabaseHas('wood_stocks', [
            'wood_type_id' => $t->id, 'grade' => 'Super 2', 'diameter' => 30, 'panjang' => 200, 'quantity' => 0,
        ]);
    }

    public function test_F03_tambah_kombinasi_duplikat(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())->post(route('stok.range.store'), [
            'wood_type_id' => $t->id, 'grade' => 'Super 1', 'diameter' => 20, 'panjang' => 130,
        ])->assertSessionHasErrors('diameter');
    }

    public function test_F04_tambah_kombinasi_panjang_tidak_valid(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())->post(route('stok.range.store'), [
            'wood_type_id' => $t->id, 'grade' => 'Super 1', 'diameter' => 20, 'panjang' => 150,
        ])->assertSessionHasErrors('panjang');
    }

    public function test_F05_tambah_kombinasi_jenis_kayu_disembunyikan(): void
    {
        [$t] = $this->kayu(0, true);
        $this->actingAs($this->admin())->post(route('stok.range.store'), [
            'wood_type_id' => $t->id, 'grade' => 'Super 1', 'diameter' => 25, 'panjang' => 130,
        ])->assertSessionHasErrors('wood_type_id');
    }

    // ========== G. Pembelian kayu ==========

    public function test_G01_form_pembelian_tampil(): void
    {
        $this->kayu();
        $this->actingAs($this->user('staff'))->get(route('wood_transactions.buy'))->assertOk();
    }

    public function test_G02_pembelian_valid_menambah_stok(): void
    {
        [$t, $s] = $this->kayu(10);
        $this->actingAs($this->user('staff'))
            ->postJson(route('wood_transactions.storeBuy'), $this->transaksiKayu($t, ['pcs' => 3]))
            ->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(13, $s->fresh()->quantity);
        $this->assertDatabaseHas('wood_transactions', ['atas_nama' => 'Pak Budi', 'jenis_transaksi' => 'Pembelian']);
    }

    public function test_G03_pembelian_kombinasi_belum_terdaftar(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())
            ->postJson(route('wood_transactions.storeBuy'), $this->transaksiKayu($t, ['diameter' => 99]))
            ->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_G04_pembelian_atas_nama_kosong(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())
            ->postJson(route('wood_transactions.storeBuy'), $this->transaksiKayu($t, [], ['atas_nama' => '']))
            ->assertStatus(422)->assertJsonValidationErrors('atas_nama');
    }

    public function test_G05_pembelian_pcs_nol(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())
            ->postJson(route('wood_transactions.storeBuy'), $this->transaksiKayu($t, ['pcs' => 0]))
            ->assertStatus(422)->assertJsonValidationErrors('details.0.pcs');
    }

    public function test_G06_pembelian_jenis_kayu_disembunyikan(): void
    {
        [$t] = $this->kayu(0, true);
        $this->actingAs($this->admin())
            ->postJson(route('wood_transactions.storeBuy'), $this->transaksiKayu($t))
            ->assertStatus(422)->assertJsonValidationErrors('wood_type_id');
    }

    public function test_G07_total_pembayaran_tidak_sesuai_rincian(): void
    {
        // Rincian bernilai 82.000 tetapi total yang dikirim hanya 1
        [$t] = $this->kayu();
        $this->actingAs($this->admin())
            ->postJson(route('wood_transactions.storeBuy'), $this->transaksiKayu($t, [], ['total_pembayaran' => 1]));
        $trx = WoodTransaction::where('atas_nama', 'Pak Budi')->latest('id')->first();
        $this->assertTrue($trx === null || (float) $trx->total_pembayaran === 82000.0,
            'Total pembayaran tersimpan ' . ($trx->total_pembayaran ?? '-') . ', seharusnya 82000 (jumlah rincian) atau ditolak.');
    }

    // ========== H. Penjualan kayu ==========

    public function test_H01_form_penjualan_tampil(): void
    {
        $this->kayu();
        $this->actingAs($this->user('staff'))->get(route('wood_transactions.sell'))->assertOk();
    }

    public function test_H02_penjualan_valid_mengurangi_stok(): void
    {
        [$t, $s] = $this->kayu(10);
        $this->actingAs($this->user('staff'))
            ->postJson(route('wood_transactions.storeSell'), $this->transaksiKayu($t, ['pcs' => 4]))
            ->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(6, $s->fresh()->quantity);
    }

    public function test_H03_penjualan_melebihi_stok(): void
    {
        [$t, $s] = $this->kayu(3);
        $this->actingAs($this->admin())
            ->postJson(route('wood_transactions.storeSell'), $this->transaksiKayu($t, ['pcs' => 5]))
            ->assertStatus(422)->assertJson(['success' => false]);
        $this->assertEquals(3, $s->fresh()->quantity);
    }

    public function test_H04_penjualan_kombinasi_tidak_ada(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())
            ->postJson(route('wood_transactions.storeSell'), $this->transaksiKayu($t, ['panjang' => 260]))
            ->assertStatus(422);
    }

    // ========== I. Potong gergaji ==========

    public function test_I01_form_potong_gergaji_tampil(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())->get(route('stock.potongGergaji.form', $t->id))->assertOk();
    }

    public function test_I02_potong_gergaji_valid(): void
    {
        [$t, $s] = $this->kayu(10);
        $this->actingAs($this->admin())->postJson(route('wood_transactions.storeSaw'),
            $this->transaksiKayu($t, ['pcs' => 2], ['jenis_transaksi' => 'gergajian']))
            ->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(8, $s->fresh()->quantity);
        $this->assertDatabaseHas('wood_transactions', ['jenis_transaksi' => 'gergajian']);
    }

    public function test_I03_potong_gergaji_melebihi_stok(): void
    {
        [$t, $s] = $this->kayu(1);
        $this->actingAs($this->admin())->postJson(route('wood_transactions.storeSaw'),
            $this->transaksiKayu($t, ['pcs' => 2], ['jenis_transaksi' => 'gergajian']))
            ->assertStatus(422);
        $this->assertEquals(1, $s->fresh()->quantity);
    }

    // ========== J. Riwayat transaksi ==========

    public function test_J01_daftar_transaksi_dan_filter(): void
    {
        [$t] = $this->kayu();
        $u = $this->admin();
        $this->actingAs($u)->postJson(route('wood_transactions.storeBuy'), $this->transaksiKayu($t));
        $this->actingAs($u)->get(route('transaksi.index', ['jenis_transaksi' => 'Pembelian', 'q' => 'Budi']))
            ->assertOk()->assertSee('Pak Budi');
    }

    public function test_J02_detail_transaksi(): void
    {
        [$t] = $this->kayu();
        $u = $this->admin();
        $url = $this->actingAs($u)->postJson(route('wood_transactions.storeBuy'), $this->transaksiKayu($t))
            ->json('redirect_url');
        $this->actingAs($u)->get($url)->assertOk()->assertSee('Pak Budi');
    }

    public function test_J03_detail_transaksi_id_tidak_valid(): void
    {
        $this->actingAs($this->admin())->get(route('transaksi.show', 'xxxxxx'))->assertNotFound();
    }

    // ========== K. Laporan transaksi kayu ==========

    public function test_K01_laporan_harian(): void
    {
        $this->actingAs($this->admin())->get(route('transaksi.laporan.harian'))->assertOk();
    }

    public function test_K02_laporan_bulanan(): void
    {
        $this->actingAs($this->admin())->get(route('transaksi.laporan.bulanan', ['bulan' => 10, 'tahun' => 2026]))->assertOk();
    }

    public function test_K03_laporan_tahunan(): void
    {
        $this->actingAs($this->admin())->get(route('transaksi.laporan.tahunan', ['tahun' => 2026]))->assertOk();
    }

    public function test_K04_laporan_bulanan_input_bulan_tidak_valid(): void
    {
        $res = $this->actingAs($this->admin())->get(route('transaksi.laporan.bulanan', ['bulan' => 'abc', 'tahun' => 2026]));
        $this->assertLessThan(500, $res->status(), 'Server error (HTTP ' . $res->status() . ')');
    }

    // ========== L. Stok kayu & export ==========

    public function test_L01_detail_stok_kayu(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->user('staff'))->get(route('stock.detail', $t->id))->assertOk();
    }

    public function test_L02_export_stok_pdf(): void
    {
        [$t] = $this->kayu();
        $this->actingAs($this->admin())->get(route('export.stok.pdf', $t->id))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_L03_detail_stok_jenis_kayu_tidak_ada(): void
    {
        $this->actingAs($this->admin())->get(route('stock.detail', 999999))->assertNotFound();
    }

    // ========== M. Ukuran & harga palet ==========

    public function test_M01_tambah_ukuran_palet_valid(): void
    {
        $this->actingAs($this->admin())->post(route('pallet-prices.store'), ['tebal' => 9, 'lebar' => 2, 'panjang' => 120])
            ->assertRedirect(route('pallet-prices.index'));
        $this->assertDatabaseHas('pallet_prices', ['ukuran' => '9x2x120']);
    }

    public function test_M02_tambah_ukuran_palet_duplikat(): void
    {
        $p = $this->palet('7x1x90');
        $this->actingAs($this->admin())->post(route('pallet-prices.store'), ['tebal' => 7, 'lebar' => 1, 'panjang' => 90])
            ->assertSessionHasErrors('tebal');
    }

    public function test_M03_ubah_harga_palet_format_rupiah(): void
    {
        $p = $this->palet();
        $this->actingAs($this->admin())->put(route('pallet-prices.update', $p->id),
            ['harga_basah' => 'Rp 1.500.000', 'harga_kering' => '2.000.000'])->assertSessionHas('success');
        $p->refresh();
        $this->assertEquals(1500000, $p->harga_basah);
        $this->assertEquals(2000000, $p->harga_kering);
    }

    public function test_M04_ubah_harga_palet_bukan_angka(): void
    {
        $p = $this->palet();
        $this->actingAs($this->admin())->put(route('pallet-prices.update', $p->id),
            ['harga_basah' => 'abc', 'harga_kering' => '1000'])->assertSessionHasErrors('harga_basah');
    }

    // ========== N. Stok palet ==========

    public function test_N01_halaman_stok_palet_tampil(): void
    {
        $p = $this->palet();
        $this->stokPalet($p, 10);
        $u = $this->user('staff');
        $this->actingAs($u)->get(route('pallet_stocks.index'))->assertOk();
        $this->actingAs($u)->get(route('pallet_stocks.create'))->assertOk();
    }

    public function test_N02_input_stok_palet_basah_valid(): void
    {
        $p = $this->palet();
        $this->actingAs($this->user('staff'))->post(route('pallet_stocks.store'), [
            'tanggal_input' => '2026-10-20', 'status' => 'basah',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => 50, 'volume' => 48]],
        ])->assertRedirect(route('pallet_stocks.index'));
        $this->assertDatabaseHas('pallet_stocks', [
            'pallet_code' => $p->pallet_code, 'pcs' => 50, 'status' => 'basah', 'input_code' => 'BSH20261020-001',
        ]);
    }

    public function test_N03_input_stok_palet_semua_pcs_kosong(): void
    {
        $p = $this->palet();
        $this->actingAs($this->admin())->post(route('pallet_stocks.store'), [
            'tanggal_input' => '2026-10-20',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => 0, 'volume' => 0]],
        ])->assertSessionHas('error');
    }

    public function test_N04_input_stok_palet_kode_tidak_terdaftar(): void
    {
        $this->actingAs($this->admin())->post(route('pallet_stocks.store'), [
            'tanggal_input' => '2026-10-20',
            'pallets' => [['pallet_code' => 'TIDAKADA', 'ukuran' => '1x1x1', 'pcs' => 5, 'volume' => 1]],
        ])->assertSessionHasErrors('pallets.0.pallet_code');
    }

    public function test_N05_input_stok_palet_status_tidak_valid(): void
    {
        $p = $this->palet();
        $res = $this->actingAs($this->admin())->post(route('pallet_stocks.store'), [
            'tanggal_input' => '2026-10-20', 'status' => 'ngawur',
            'pallets' => [['pallet_code' => $p->pallet_code, 'ukuran' => $p->ukuran, 'pcs' => 5, 'volume' => 4.8]],
        ]);
        $this->assertLessThan(500, $res->status(), 'Server error (HTTP ' . $res->status() . ')');
        $res->assertSessionHasErrors('status');
        $this->assertDatabaseMissing('pallet_stocks', ['status' => 'ngawur']);
    }

    public function test_N06_pindah_status_basah_penjemuran_kering(): void
    {
        $p = $this->palet();
        $s = $this->stokPalet($p, 10);
        $u = $this->admin();

        $this->actingAs($u)->post(route('stok_palet.moveToNextStatus', $s->input_code))->assertSessionHas('success');
        $s->refresh();
        $this->assertSame('penjemuran', $s->status);
        $this->assertStringStartsWith('JMR', $s->input_code);

        $this->actingAs($u)->post(route('stok_palet.moveToNextStatus', $s->input_code))->assertSessionHas('success');
        $s->refresh();
        $this->assertSame('kering', $s->status);
        $this->assertStringStartsWith('KRG', $s->input_code);
    }

    public function test_N07_pindah_status_dari_kering(): void
    {
        $p = $this->palet();
        $s = $this->stokPalet($p, 10, 'kering');
        $this->actingAs($this->admin())->post(route('stok_palet.moveToNextStatus', $s->input_code))
            ->assertSessionHas('error');
        $this->assertSame('kering', $s->fresh()->status);
    }

    // ========== P. Penjualan palet ==========

    private function jualPalet(PalletPrice $p, int $pcs, string $status = 'basah', array $over = []): array
    {
        return array_merge([
            'tanggal' => '2026-10-24', 'customer' => 'CV Uji', 'keterangan' => '',
            $status => [[
                'ukuran' => $p->ukuran, 'pcs' => $pcs, 'volume' => $pcs * $p->volume_per_pcs,
                'harga_satuan' => 1000, 'subtotal' => number_format($pcs * 1000, 0, ',', '.'),
            ]],
        ], $over);
    }

    public function test_P01_form_dan_daftar_penjualan_palet_tampil(): void
    {
        $this->palet();
        $u = $this->user('staff');
        $this->actingAs($u)->get(route('pallet-sales.create'))->assertOk();
        $this->actingAs($u)->get(route('pallet-sales.index'))->assertOk();
    }

    public function test_P02_penjualan_palet_basah_fifo(): void
    {
        $p = $this->palet();
        $lama = $this->stokPalet($p, 5, 'basah', '2026-10-01');
        $baru = $this->stokPalet($p, 10, 'basah', '2026-10-10');

        $this->actingAs($this->user('staff'))->post(route('pallet-sales.store'), $this->jualPalet($p, 7))
            ->assertRedirect(route('pallet-sales.index'));

        $this->assertDatabaseMissing('pallet_stocks', ['id' => $lama->id]); // stok lama habis duluan
        $this->assertEquals(8, $baru->fresh()->pcs);
        $this->assertDatabaseHas('pallet_sales', ['customer' => 'CV Uji']);
    }

    public function test_P03_penjualan_palet_melebihi_stok(): void
    {
        $p = $this->palet();
        $s = $this->stokPalet($p, 3);
        $this->actingAs($this->admin())->post(route('pallet-sales.store'), $this->jualPalet($p, 10))
            ->assertSessionHas('error');
        $this->assertEquals(3, $s->fresh()->pcs);
        $this->assertDatabaseMissing('pallet_sales', ['customer' => 'CV Uji']);
    }

    public function test_P04_penjualan_palet_tanpa_item_menampilkan_pesan(): void
    {
        $p = $this->palet();
        $u = $this->admin();
        $this->actingAs($u)->from(route('pallet-sales.create'))
            ->post(route('pallet-sales.store'), $this->jualPalet($p, 0));
        // Pesan harus terlihat di halaman form
        $this->actingAs($u)->get(route('pallet-sales.create'))->assertSee('Minimal 1 item palet');
    }

    public function test_P05_penjualan_palet_customer_kosong(): void
    {
        $p = $this->palet();
        $this->stokPalet($p, 10);
        $this->actingAs($this->admin())->post(route('pallet-sales.store'), $this->jualPalet($p, 1, 'basah', ['customer' => '']))
            ->assertSessionHasErrors('customer');
    }

    public function test_P06_volume_stok_palet_ikut_berkurang_setelah_dijual(): void
    {
        $p = $this->palet(); // 0.96 per pcs
        $s = $this->stokPalet($p, 10); // volume 9.6
        $this->actingAs($this->admin())->post(route('pallet-sales.store'), $this->jualPalet($p, 4));
        $s->refresh();
        $this->assertEquals(6, $s->pcs);
        $this->assertEqualsWithDelta(5.76, (float) $s->volume, 0.001, "Volume stok masih {$s->volume} m³ padahal sisa 6 pcs.");
    }

    public function test_P07_daftar_penjualan_palet_parameter_urut_tidak_valid(): void
    {
        $res = $this->actingAs($this->admin())->get(route('pallet-sales.index', ['sort' => 'kolom_asal', 'direction' => 'acak']));
        $this->assertLessThan(500, $res->status(), 'Server error (HTTP ' . $res->status() . ')');
    }

    public function test_P08_nota_dan_laporan_mingguan_pdf(): void
    {
        $p = $this->palet();
        $this->stokPalet($p, 10);
        $u = $this->admin();
        $this->actingAs($u)->post(route('pallet-sales.store'), $this->jualPalet($p, 2, 'basah', ['tanggal' => now()->toDateString()]));
        $sale = PalletSale::latest('id')->first();

        $this->actingAs($u)->get(route('pallet-sales.nota', $sale->id))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($u)->get(route('pallet-sales.laporan-mingguan'))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_P09_nota_penjualan_id_tidak_ada(): void
    {
        $this->actingAs($this->admin())->get(route('pallet-sales.nota', 999999))->assertNotFound();
    }
}
