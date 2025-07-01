<?php

namespace Database\Seeders;

use App\Models\PalletPrice;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            WoodTypeSeeder::class,
            GradeSeeder::class,
            DiameterSeeder::class,
            WoodStockSeeder::class,
            WoodPriceSeeder::class,
            PalletPriceSeeder::class,
        ]);
    }
}
