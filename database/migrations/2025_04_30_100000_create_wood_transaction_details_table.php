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
        Schema::create('wood_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wood_transaction_id')->constrained('wood_transactions')->onDelete('cascade'); // Relasi ke tabel utama
            $table->foreignId('jenis_kayu_id')->constrained('wood_types')->onDelete('cascade'); // Jenis kayu
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade'); // Grade kayu
            $table->integer('diameter'); // Diameter kayu
            $table->integer('panjang'); // Panjang kayu
            $table->integer('quantity'); // Jumlah kayu
            $table->decimal('volume', 10, 3); // Volume per pcs
            $table->decimal('price_per_m3', 12, 2); // Harga per m³
            $table->decimal('subtotal_volume', 10, 3); // Subtotal volume
            $table->decimal('subtotal_price', 15, 2); // Subtotal harga
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wood_transaction_details');
    }
};