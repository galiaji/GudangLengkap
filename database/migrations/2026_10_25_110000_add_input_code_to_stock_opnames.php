<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stock opname pallet dilakukan per batch: simpan kode batch yang dihitung
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->string('input_code')->nullable()->after('pallet_status');
        });
    }

    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn('input_code');
        });
    }
};
