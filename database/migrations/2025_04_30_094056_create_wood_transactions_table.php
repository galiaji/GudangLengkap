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
            $table->foreignId('jenis_kayu_id')->constrained('wood_types')->onDelete('cascade'); // Pastikan tabel 'wood_types' ada
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade'); // Pastikan tabel 'grades' ada
            $table->unsignedInteger('diameter');
            $table->unsignedInteger('panjang'); // Tambahkan ini
            $table->unsignedInteger('quantity');
            $table->decimal('volume', 8, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
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
