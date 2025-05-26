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
        Schema::create('pallet_prices', function (Blueprint $table) {
            $table->id();
            $table->string('pallet_code')->unique();
            $table->string('ukuran'); // contoh: "8x1.2x100"
            $table->decimal('harga_basah', 15, 2)->nullable();
            $table->decimal('harga_kering', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallet_prices');
    }
};