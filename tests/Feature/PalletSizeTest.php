<?php

namespace Tests\Feature;

use App\Models\PalletPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PalletSizeTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::create([
            'name'     => 'Admin Test',
            'username' => 'admintest',
            'email'    => 'admin@test.local',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);
    }

    public function test_form_tambah_ukuran_bisa_dibuka(): void
    {
        $this->actingAs($this->admin())
            ->get(route('pallet-prices.create'))
            ->assertOk()
            ->assertViewIs('stok_palet.createUkuran')
            ->assertViewHas('nextCode');
    }

    public function test_admin_bisa_menambah_ukuran_baru(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('pallet-prices.store'), [
                'lebar'   => 8,
                'tebal'   => 1.2,
                'panjang' => 100,
            ]);

        $response->assertRedirect(route('pallet-prices.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pallet_prices', [
            'ukuran'       => '8x1.2x100',
            'pallet_code'  => 'A1',
            'harga_basah'  => 0,
            'harga_kering' => 0,
        ]);
        $this->assertEquals(0.96, PalletPrice::first()->volume_per_pcs);
    }

    public function test_ukuran_duplikat_ditolak(): void
    {
        $admin = $this->admin();
        $data = ['lebar' => 8, 'tebal' => 1.2, 'panjang' => 100];

        $this->actingAs($admin)->post(route('pallet-prices.store'), $data);
        $this->actingAs($admin)->post(route('pallet-prices.store'), $data)
            ->assertSessionHasErrors('tebal');

        $this->assertEquals(1, PalletPrice::count());
    }

    public function test_ukuran_dengan_urutan_dimensi_berbeda_ditolak(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('pallet-prices.store'), ['lebar' => 8, 'tebal' => 1.2, 'panjang' => 100]);

        // Urutan lain dari 8, 1.2, 100 (dengan lebar >= tebal) dianggap ukuran yang sama
        foreach ([[100, 1.2, 8], [100, 8, 1.2]] as [$l, $t, $p]) {
            $this->actingAs($admin)->post(route('pallet-prices.store'), ['lebar' => $l, 'tebal' => $t, 'panjang' => $p])
                ->assertSessionHasErrors(['tebal' => "Ukuran {$l}x{$t}x{$p} sudah terdaftar sebagai 8x1.2x100 (kombinasi dimensi sama, urutan berbeda)."]);
        }
        $this->assertEquals(1, PalletPrice::count());

        // Kombinasi dimensi yang berbeda tetap boleh
        $this->actingAs($admin)->post(route('pallet-prices.store'), ['lebar' => 8, 'tebal' => 1.2, 'panjang' => 130])
            ->assertSessionHasNoErrors();
        $this->assertEquals(2, PalletPrice::count());
    }

    public function test_hapus_ukuran_tanpa_stok_dan_penjualan(): void
    {
        $p = PalletPrice::create(['pallet_code' => 'A1', 'ukuran' => '1.2x100x8', 'harga_basah' => 0, 'harga_kering' => 0]);

        $this->actingAs($this->admin())->delete(route('pallet-prices.destroy', $p->id))
            ->assertRedirect(route('pallet-prices.index'))
            ->assertSessionHas('success', 'Ukuran 1.2x100x8 berhasil dihapus.');
        $this->assertEquals(0, PalletPrice::count());
    }

    public function test_hapus_ukuran_yang_masih_dipakai_ditolak(): void
    {
        $admin = $this->admin();
        $stok  = PalletPrice::create(['pallet_code' => 'A1', 'ukuran' => '8x1.2x100', 'harga_basah' => 0, 'harga_kering' => 0]);
        \App\Models\PalletStock::create(['pallet_code' => 'A1', 'ukuran' => '8x1.2x100', 'pcs' => 5, 'volume' => 0.005,
            'status' => 'basah', 'input_code' => 'BSH-T1', 'tanggal_input' => '2026-10-01']);
        $jual = PalletPrice::create(['pallet_code' => 'A2', 'ukuran' => '10x1.2x100', 'harga_basah' => 0, 'harga_kering' => 0]);
        $sale = \App\Models\PalletSale::create(['tanggal' => '2026-10-01', 'customer' => 'CV Uji']);
        \App\Models\PalletSaleItem::create(['pallet_sale_id' => $sale->id, 'pallet_code' => 'A2', 'ukuran' => '10x1.2x100',
            'status' => 'basah', 'pcs' => 1, 'volume' => 0.001, 'subtotal' => 0]);

        $this->actingAs($admin)->delete(route('pallet-prices.destroy', $stok->id))
            ->assertSessionHas('error', 'Ukuran 8x1.2x100 tidak bisa dihapus karena masih ada stok palet dengan ukuran ini.');
        $this->actingAs($admin)->delete(route('pallet-prices.destroy', $jual->id))
            ->assertSessionHas('error', 'Ukuran 10x1.2x100 tidak bisa dihapus karena sudah dipakai pada transaksi penjualan.');
        $this->assertEquals(2, PalletPrice::count());
    }

    public function test_staff_tidak_bisa_menghapus_ukuran(): void
    {
        $p = PalletPrice::create(['pallet_code' => 'A1', 'ukuran' => '8x1.2x100', 'harga_basah' => 0, 'harga_kering' => 0]);
        $staff = User::create(['name' => 'Staf', 'username' => 'staf_hapus', 'email' => 'staf_hapus@test.local',
            'password' => bcrypt('rahasia123'), 'role' => 'staff']);

        $this->actingAs($staff)->delete(route('pallet-prices.destroy', $p->id))->assertForbidden();
        $this->assertEquals(1, PalletPrice::count());
    }

    public function test_lebar_lebih_kecil_dari_tebal_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('pallet-prices.store'), ['lebar' => 1.2, 'tebal' => 8, 'panjang' => 100])
            ->assertSessionHasErrors(['lebar' => 'Lebar tidak boleh lebih kecil dari tebal.']);
        $this->assertEquals(0, PalletPrice::count());
    }

    public function test_isian_kosong_atau_nol_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('pallet-prices.store'), ['tebal' => 0, 'lebar' => '', 'panjang' => -5])
            ->assertSessionHasErrors(['tebal', 'lebar', 'panjang']);

        $this->assertEquals(0, PalletPrice::count());
    }
}
