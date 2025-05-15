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
        $woodTypes = ['Mahoni', 'Keras', 'Sengon'];
        $grades = [
            'Rijek 1', 'Rijek 2',
            'Storty 1', 'Storty 2',
            'Super 1', 'Super 2'
        ];
        $lengths = [100, 130, 200, 260];

        foreach ($woodTypes as $type) {
            foreach ($grades as $grade) {
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
