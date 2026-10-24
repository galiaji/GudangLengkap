<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wood_types', function (Blueprint $table) {
            // Jenis kayu yang disembunyikan tidak muncul di form & dashboard,
            // tapi riwayat transaksinya tetap ada.
            $table->boolean('is_hidden')->default(false)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('wood_types', function (Blueprint $table) {
            $table->dropColumn('is_hidden');
        });
    }
};
