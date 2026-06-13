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
        Schema::create('pallet_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('pallet_code');
            $table->string('ukuran');
            $table->integer('pcs');
            $table->decimal('volume', 10, 3);
            $table->enum('status', ['basah', 'penjemuran', 'kering']);
            $table->string('input_code')->nullable(); // gunakan lowercase agar konsisten
            $table->date('tanggal_input');
            $table->timestamps();

            $table->foreign('pallet_code')->references('pallet_code')->on('pallet_prices')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallet_stocks');
    }
};
