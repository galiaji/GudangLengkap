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
            $table->decimal('volume', 10, 3); // Volume kayu
            $table->decimal('price', 12, 2); // Harga kayu
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