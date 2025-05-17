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
                    $pricePerM3 = 450000; // Atur harga sesuai kebutuhan

                    DB::table('wood_prices')->updateOrInsert([
                        'wood_type' => $type,
                        'grade' => $grade,
                        'panjang' => $length,
                    ], [
                        'price_per_m3' => $pricePerM3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
