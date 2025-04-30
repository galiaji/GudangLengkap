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
        // Ambil data grades dari tabel grades
        $grades = DB::table('grades')->get();

        // Ambil semua wood types dari tabel wood_types
        $woodTypes = DB::table('wood_types')->get();

        // Daftar panjang kayu yang ingin dimasukkan
        $lengths = [100, 130, 200, 260]; // Contoh panjang kayu

        // Loop melalui setiap wood type, grade, dan panjang
        foreach ($woodTypes as $woodType) {
            foreach ($grades as $grade) {
                foreach ($lengths as $length) {
                    // Ambil rentang diameter dari grade
                    $minDiameter = $grade->min_diameter;
                    $maxDiameter = $grade->max_diameter;

                    // Ambil diameter minimum sebagai contoh
                    $diameter = $minDiameter;

                    // Ambil volume dari tabel diameters berdasarkan diameter dan panjang
                    $volume = DB::table('diameters')
                        ->where('diameter', $diameter)
                        ->where('panjang', $length)
                        ->value('volume');

                    // Jika volume ditemukan, periksa apakah data sudah ada
                    if ($volume !== null) {
                        $exists = DB::table('wood_stocks')
                            ->where('wood_type_id', $woodType->id)
                            ->where('grade_id', $grade->id)
                            ->where('diameter', $diameter)
                            ->where('panjang', $length)
                            ->exists();

                        // Jika data belum ada, masukkan ke tabel wood_stocks
                        if (!$exists) {
                            DB::table('wood_stocks')->insert([
                                'wood_type_id' => $woodType->id,
                                'grade_id' => $grade->id,
                                'diameter' => $diameter,
                                'panjang' => $length,
                                'quantity' => 0, // Jumlah default
                                'total_volume' => $volume * 10, // Total volume dihitung (contoh: quantity * volume)
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
