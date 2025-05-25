<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WoodPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Allowed length per grade (samakan dengan stock)
        $allowedLengthsByGrade = [
            'Rijek 1'   => [100, 130],
            'Rijek 2'   => [100, 130],
            'Storty 1'  => [130],
            'Storty 2'  => [130],
            'Super 1'   => [130, 200],
            'Super 2'   => [130, 200],
        ];

        $woodTypes = ['Mahoni', 'Keras', 'Sengon'];

        foreach ($woodTypes as $type) {
            foreach ($allowedLengthsByGrade as $grade => $lengths) {
                foreach ($lengths as $length) {
                    $hargaBeli = 400000; // Contoh harga beli
                    $hargaJual = 450000; // Contoh harga jual

                    DB::table('wood_prices')->updateOrInsert([
                        'wood_type' => $type,
                        'grade' => $grade,
                        'panjang' => $length,
                    ], [
                        'harga_beli_per_m3' => $hargaBeli,
                        'harga_jual_per_m3' => $hargaJual,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
