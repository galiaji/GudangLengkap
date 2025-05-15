<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up()
    // {
    //     Schema::create('wood_transactions', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('seller_name'); // Nama penjual
    //         $table->decimal('transaction_total_volume', 12, 3)->default(0); // Grand total volume
    //         $table->decimal('transaction_total_price', 15, 2)->default(0); // Grand total pembelian
    //         $table->timestamps(); 
    //     });
    // }

    public function up()
    {
        Schema::create('wood_transactions', function (Blueprint $table) {
            $table->id(); // Primary Key otomatis
            $table->string('jenis_transaksi'); // Jenis Transaksi
            $table->string('atas_nama'); // Nama Pemilik
            $table->decimal('total_volume', 10, 3); // Volume dalam m3
            $table->decimal('total_pembayaran', 15, 2); // Total Pembayaran
            $table->dateTime('tanggal_transaksi'); // Tanggal Transaksi
            $table->timestamps(); // Kolom created_at & updated_at otomatis
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wood_transactions');
    }
};
