<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\WoodPrice;

class AddLength260WoodStockSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            'Super 1' => [19, 20, 21, 22, 23, 24, 25],
            'Super 2' => [26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40],
        ];

        $woodTypes = ['Mahoni', 'Keras', 'Sengon'];

        $lengths = [130, 200, 260];

        
        $volumes = [
            130 => [
            19 => 37,
            20 => 41,
            21 => 45,
            22 => 49,
            23 => 53,
            24 => 58,
            25 => 63,
            26 => 68,
            27 => 74,
            28 => 80,
            29 => 86,
            30 => 92,
            31 => 98,
            32 => 105,
            33 => 112,
            34 => 119,
            35 => 126,
            36 => 133,
            37 => 141,
            38 => 149,
            39 => 157,
            40 => 164,
        ],
            200 => [
                19 => 56,
                20 => 62,
                21 => 69,
                22 => 76,
                23 => 83,
                24 => 90,
                25 => 98,
                26 => 106,
                27 => 114,
                28 => 123,
                29 => 132,
                30 => 141,
                31 => 151,
                32 => 161,
                33 => 172,
                34 => 182,
                35 => 193,
                36 => 204,
                37 => 216,
                38 => 227,
                39 => 239,
                40 => 252,
            ],

            260 => [
                19 => 74,
                20 => 82,
                21 => 90,
                22 => 99,
                23 => 108,
                24 => 117,
                25 => 127,
                26 => 138,
                27 => 149,
                28 => 160,
                29 => 172,
                30 => 184,
                31 => 196,
                32 => 209,
                33 => 223,
                34 => 236,
                35 => 250,
                36 => 265,
                37 => 279,
                38 => 294,
                39 => 310,
                40 => 327,
            ]
        ];
        foreach ($woodTypes as $woodType) {

            foreach ($grades as $grade => $diameters) {

                foreach ($lengths as $length) {

                    $price = WoodPrice::firstOrCreate(
                        [
                            'wood_type' => $woodType,
                            'grade'     => $grade,
                            'panjang'   => $length,
                        ],
                        [
                            'harga_beli_per_m3' => 0,
                            'harga_jual_per_m3' => 0,
                        ]
                    );

                    foreach ($diameters as $diameter) {

                        DB::table('wood_stocks')->insertOrIgnore([
                            'wood_type'     => $woodType,
                            'grade'         => $grade,
                            'diameter'      => $diameter,
                            'panjang'       => $length,
                            'quantity'      => 0,
                            'total_volume'  => 0,
                            'volume'        => ($volumes[$length][$diameter] ?? 0) / 1000,
                            'wood_price_id' => $price->id,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }
        }
    }
}