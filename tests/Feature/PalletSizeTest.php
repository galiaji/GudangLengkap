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
            ->assertViewIs('stok_palet.createHarga')
            ->assertViewHas('nextCode');
    }

    public function test_admin_bisa_menambah_ukuran_baru(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('pallet-prices.store'), [
                'tebal'   => 8,
                'lebar'   => 1.2,
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
        $data = ['tebal' => 8, 'lebar' => 1.2, 'panjang' => 100];

        $this->actingAs($admin)->post(route('pallet-prices.store'), $data);
        $this->actingAs($admin)->post(route('pallet-prices.store'), $data)
            ->assertSessionHasErrors('tebal');

        $this->assertEquals(1, PalletPrice::count());
    }

    public function test_isian_kosong_atau_nol_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('pallet-prices.store'), ['tebal' => 0, 'lebar' => '', 'panjang' => -5])
            ->assertSessionHasErrors(['tebal', 'lebar', 'panjang']);

        $this->assertEquals(0, PalletPrice::count());
    }
}
