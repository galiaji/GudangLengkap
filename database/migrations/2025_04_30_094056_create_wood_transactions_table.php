<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('wood_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('seller_name'); // Nama penjual
            $table->decimal('transaction_total_volume', 12, 3)->default(0); // Grand total volume
            $table->decimal('transaction_total_price', 15, 2)->default(0); // Grand total pembelian
            $table->timestamps(); 
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
