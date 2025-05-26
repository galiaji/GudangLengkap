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
        Schema::create('wet_pallet_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('pallet_code');
            $table->string('ukuran');
            $table->integer('pcs');
            $table->decimal('volume', 10, 3);
            $table->date('entry_date');
            $table->timestamps();

            $table->foreign('pallet_code')->references('pallet_code')->on('pallet_prices')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wet_pallet_stocks');
    }
};