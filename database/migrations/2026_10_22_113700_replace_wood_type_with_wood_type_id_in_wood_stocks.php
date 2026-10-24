<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ganti kolom teks wood_stocks.wood_type dengan relasi wood_type_id.
 * Di database yang sudah diubah manual (kolom wood_type sudah tidak ada),
 * migrasi ini tidak melakukan apa-apa.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('wood_stocks', 'wood_type')) {
            return;
        }

        // 1. Pastikan setiap nama jenis kayu lama ada di tabel wood_types
        DB::statement("
            INSERT INTO wood_types (name, created_at, updated_at)
            SELECT DISTINCT s.wood_type, NOW(), NOW()
            FROM wood_stocks s
            LEFT JOIN wood_types t ON t.name = s.wood_type
            WHERE t.id IS NULL
        ");

        // 2. Isi wood_type_id berdasarkan nama
        DB::statement("
            UPDATE wood_stocks s
            JOIN wood_types t ON t.name = s.wood_type
            SET s.wood_type_id = t.id
            WHERE s.wood_type_id IS NULL
        ");

        // 3. Ganti unique key lalu hapus kolom lama
        Schema::table('wood_stocks', function (Blueprint $table) {
            $table->dropUnique(['wood_type', 'grade', 'diameter', 'panjang']);
            $table->unique(['wood_type_id', 'grade', 'diameter', 'panjang'], 'wood_stocks_unique');
            $table->dropColumn('wood_type');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('wood_stocks', 'wood_type')) {
            return;
        }

        Schema::table('wood_stocks', function (Blueprint $table) {
            $table->string('wood_type')->default('')->after('wood_type_id');
        });

        DB::statement("
            UPDATE wood_stocks s
            JOIN wood_types t ON t.id = s.wood_type_id
            SET s.wood_type = t.name
        ");

        Schema::table('wood_stocks', function (Blueprint $table) {
            // Foreign key wood_type_id butuh index sendiri sebelum unique key dihapus
            $table->index('wood_type_id', 'wood_stocks_wood_type_id_foreign');
            $table->dropUnique('wood_stocks_unique');
            $table->unique(['wood_type', 'grade', 'diameter', 'panjang']);
        });
    }
};
