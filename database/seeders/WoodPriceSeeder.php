<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WoodType; // Import the WoodType model
use App\Models\Grade;
use App\Models\WoodPrice; // Import the WoodPrice model

class WoodPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $woodTypes = WoodType::all(); // Jenis kayu
        $grades = Grade::all(); // Grade
        $lengths = [100, 130, 200, 260]; // Panjang yang berbeda

        foreach ($woodTypes as $type) { // Iterasi jenis kayu
            foreach ($grades as $grade) { // Iterasi grade
                foreach ($lengths as $length) { // Iterasi panjang
                    $pricePerM3 = 450000; // Harga per m3 random antara 500,000 dan 2,000,000

                    WoodPrice::updateOrCreate([
                        'wood_type_id' => $type->id,
                        'grade_id' => $grade->id,
                        'panjang' => $length, // Panjang sebagai bagian dari kombinasi unik
                    ], [
                        'price_per_m3' => $pricePerM3,
                    ]);
                }
            }
        }
    }
}
