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
            $table->foreignId('wood_transaction_id')->constrained('wood_transactions')->onDelete('cascade');
            $table->string('wood_type');
            $table->string('grade');
            $table->decimal('diameter', 5, 2);
            $table->integer('panjang');
            $table->integer('pcs');
            $table->decimal('volume', 10, 3);
            $table->decimal('harga', 15, 2);
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