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
        Schema::create('wood_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('diameter');
            $table->unsignedInteger('panjang');
            $table->foreignId('wood_type_id')->constrained('wood_types')->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('total_volume', 8, 2)->default(0);
            $table->unique(['wood_type_id', 'grade_id', 'diameter', 'panjang']);
            $table->timestamps();

            // Tambahkan indeks untuk performa
            $table->index('diameter');
            $table->index('grade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wood_stocks');
    }
};
