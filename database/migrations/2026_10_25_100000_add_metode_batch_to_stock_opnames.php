<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Metode hitung: "ukuran" (total per ukuran, selisih FIFO) atau "batch" (per batch x ukuran)
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->string('metode', 10)->default('ukuran')->after('pallet_status');
        });

        // Kode batch palet yang dihitung; null = per ukuran atau baris temuan tanpa batch
        Schema::table('stock_opname_items', function (Blueprint $table) {
            $table->string('input_code')->nullable()->after('pallet_code');
        });
    }

    public function down(): void
    {
        Schema::table('stock_opname_items', function (Blueprint $table) {
            $table->dropColumn('input_code');
        });
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn('metode');
        });
    }
};
