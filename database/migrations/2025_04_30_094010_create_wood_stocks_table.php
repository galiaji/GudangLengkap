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
            $table->string('wood_type'); // ubah dari wood_type_id
            $table->string('grade');     // ubah dari grade_id
            $table->unsignedInteger('diameter');
            $table->unsignedInteger('panjang');
            $table->decimal('volume', 8, 3)->default(0);
            $table->unsignedInteger('quantity')->nullable();
            $table->decimal('total_volume', 8, 3)->default(0);
            $table->unique(['wood_type', 'grade', 'diameter', 'panjang']);
            $table->timestamps();

            $table->index('diameter');
            $table->index('grade');
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
