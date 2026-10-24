<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pallet_prices', function (Blueprint $table) {
            // Volume per pcs (satuan sama dengan form stok palet).
            // Null = dihitung otomatis dari ukuran: (T x L x P) / 1000
            $table->decimal('volume_per_pcs', 12, 6)->nullable()->after('ukuran');
        });

        // Isi ukuran lama dengan nilai yang selama ini dipakai di form
        $lama = [
            '8x1.2x100'  => 0.96,
            '8x1.2x130'  => 1.248,
            '10x1.2x100' => 1.2,
            '10x1.2x130' => 1.56,
            '12x1.2x100' => 1.44,
            '12x1.2x130' => 1.872,
            '15x1.3x100' => 1.8,
            '15x1.3x130' => 2.34,
        ];
        foreach ($lama as $ukuran => $volume) {
            DB::table('pallet_prices')->where('ukuran', $ukuran)->update(['volume_per_pcs' => $volume]);
        }
    }

    public function down(): void
    {
        Schema::table('pallet_prices', function (Blueprint $table) {
            $table->dropColumn('volume_per_pcs');
        });
    }
};
