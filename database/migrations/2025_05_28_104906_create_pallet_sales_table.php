<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabel transaksi penjualan palet (header)
        Schema::create('pallet_sales', function (Blueprint $table) {
            $table->id();
            // $table->string('kode_transaksi')->unique();
            $table->date('tanggal');
            $table->string('customer')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Tabel detail item palet yang dijual
        Schema::create('pallet_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pallet_sale_id')->constrained('pallet_sales')->onDelete('cascade');
            $table->string('pallet_code');
            $table->string('ukuran');
            $table->enum('status', ['basah', 'penjemuran', 'kering']);
            $table->integer('pcs');
            $table->decimal('volume', 10, 3);
            // $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();

            $table->foreign('pallet_code')->references('pallet_code')->on('pallet_prices')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallet_sale_items');
        Schema::dropIfExists('pallet_sales');
    }
};
