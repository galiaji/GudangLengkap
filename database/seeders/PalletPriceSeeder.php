<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PalletPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pallet_prices')->insert([
            [
                'pallet_code' => 'A1',
                'ukuran' => '8x1.2x100',
                'harga_basah' => 1000000,
                'harga_kering' => 1050000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pallet_code' => 'A2',
                'ukuran' => '8x1.2x130',
                'harga_basah' => 1200000,
                'harga_kering' => 1250000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pallet_code' => 'A3',
                'ukuran' => '10x1.2x100',
                'harga_basah' => 1200000,
                'harga_kering' => 1250000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pallet_code' => 'A4',
                'ukuran' => '10x1.2x130',
                'harga_basah' => 1200000,
                'harga_kering' => 1250000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pallet_code' => 'A5',
                'ukuran' => '12x1.2x100',
                'harga_basah' => 1700000,
                'harga_kering' => 1750000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pallet_code' => 'A6',
                'ukuran' => '12x1.2x130',
                'harga_basah' => 1700000,
                'harga_kering' => 1750000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pallet_code' => 'A7',
                'ukuran' => '15x1.3x100',
                'harga_basah' => 2000000,
                'harga_kering' => 2050000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pallet_code' => 'A8',
                'ukuran' => '15x1.3x130',
                'harga_basah' => 2000000,
                'harga_kering' => 2050000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}