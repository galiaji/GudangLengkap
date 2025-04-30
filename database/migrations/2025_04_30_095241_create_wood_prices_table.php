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
            $table->foreignId('wood_type_id')->constrained('wood_types')->onDelete('cascade'); // Pastikan tabel 'wood_types' ada
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade'); // Pastikan tabel 'grades' ada
            // $table->foreignId('diameter_id')->constrained('diameters')->onDelete('cascade'); // Pastikan tabel 'diameters' ada
            $table->unsignedInteger('panjang');
            $table->decimal('price_per_m3', 12, 2);
            $table->timestamps();
        
            $table->unique(['wood_type_id', 'grade_id', 'panjang']);
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
