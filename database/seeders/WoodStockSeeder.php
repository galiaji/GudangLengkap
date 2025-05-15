<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WoodStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gradesArr = [
            'Rijek 1' => [10, 11, 12, 14, 15],
            'Rijek 2' => [16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30],
            'Storty 1' => [13, 14, 15],
            'Storty 2' => [16, 17, 18],
            'Super 1' => [19, 20, 21, 22, 23, 24],
            'Super 2' => [25, 26, 27, 28, 29, 30],
        ];

        $allowedLengths = [
            'Rijek' => [100, 130],
            'Storty' => [130],
            'Super' => [130, 200],
        ];

        // Volume per batang (diameter x panjang)
        $volumes = [
            10 => [100 => 8, 130 => 10, 200 => 16],
            11 => [100 => 9, 130 => 12, 200 => 18],
            12 => [100 => 11, 130 => 14, 200 => 22],
            13 => [100 => 13, 130 => 17, 200 => 26],
            14 => [100 => 15, 130 => 20, 200 => 30],
            15 => [100 => 17, 130 => 23, 200 => 35],
            16 => [100 => 20, 130 => 26, 200 => 40],
            17 => [100 => 22, 130 => 29, 200 => 45],
            18 => [100 => 25, 130 => 33, 200 => 50],
            19 => [100 => 28, 130 => 37, 200 => 56],
            20 => [100 => 31, 130 => 41, 200 => 62],
            21 => [100 => 34, 130 => 45, 200 => 69],
            22 => [100 => 38, 130 => 49, 200 => 76],
            23 => [100 => 41, 130 => 53, 200 => 83],
            24 => [100 => 45, 130 => 58, 200 => 90],
            25 => [100 => 49, 130 => 63, 200 => 98],
            26 => [100 => 53, 130 => 68, 200 => 106],
            27 => [100 => 57, 130 => 74, 200 => 114],
            28 => [100 => 61, 130 => 80, 200 => 123],
            29 => [100 => 66, 130 => 86, 200 => 132],
            30 => [100 => 70, 130 => 92, 200 => 141],
        ];

        foreach (['Mahoni', 'Keras', 'Sengon'] as $woodType) {
            foreach ($gradesArr as $gradeName => $diameters) {
                $prefix = explode(' ', $gradeName)[0];
                $allowed = $allowedLengths[$prefix] ?? [];

                foreach ($diameters as $diameter) {
                    foreach ($allowed as $length) {
                        // Ambil volume dari array $volumes
                        $volume = isset($volumes[$diameter][$length]) ? $volumes[$diameter][$length] : null;

                        if ($volume !== null) {
                            $exists = DB::table('wood_stocks')
                                ->where('wood_type', $woodType)
                                ->where('grade', $gradeName)
                                ->where('diameter', $diameter)
                                ->where('panjang', $length)
                                ->exists();

                            if (!$exists) {
                                DB::table('wood_stocks')->insert([
                                    'wood_type' => $woodType,
                                    'grade' => $gradeName,
                                    'diameter' => $diameter,
                                    'panjang' => $length,
                                    'quantity' => 0,
                                    'total_volume' => 0,
                                    'volume' => $volume / 1000,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}
