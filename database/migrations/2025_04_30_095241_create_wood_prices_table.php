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
        Schema::create('wood_prices', function (Blueprint $table) {
            $table->id();
            $table->string('wood_type');
            $table->string('grade');
            $table->unsignedInteger('panjang');
            $table->decimal('price_per_m3', 12, 2);
            $table->timestamps();

            $table->unique(['wood_type', 'grade', 'panjang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wood_prices');
    }
};
