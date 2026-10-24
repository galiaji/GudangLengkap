<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sesi stock opname
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();               // contoh SO-20261024-01
            $table->date('tanggal');
            $table->string('jenis', 10);                    // kayu / palet
            $table->foreignId('wood_type_id')->nullable()->constrained('wood_types')->nullOnDelete();
            $table->string('pallet_status', 20)->nullable(); // basah / penjemuran / kering
            $table->string('cakupan');                      // label, contoh "Kayu Mahoni" / "Palet Basah"
            $table->string('status', 20)->default('menghitung'); // menghitung / menunggu / selesai / batal
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'status']);
        });

        // Rincian item yang dihitung
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('wood_stock_id')->nullable()->constrained('wood_stocks')->nullOnDelete(); // untuk kayu
            $table->string('pallet_code')->nullable();      // untuk palet
            $table->string('label');                        // contoh "Super 1 · Ø20 · P130" / "8x1.2x100"
            $table->decimal('volume_per_pcs', 12, 6)->default(0);
            $table->decimal('harga', 15, 2)->default(0);    // harga per m³ saat opname dimulai
            $table->integer('qty_sistem');                  // snapshot saat stok dikunci
            $table->integer('qty_fisik')->nullable();       // hasil hitung
            $table->integer('selisih')->nullable();         // fisik - sistem
            $table->decimal('volume_selisih', 12, 3)->nullable();
            $table->decimal('nilai_selisih', 15, 2)->nullable();
            $table->string('alasan')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
