<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wood_transaction_details', function (Blueprint $table) {
            // Tambahkan foreign key ke wood_stocks
            if (!Schema::hasColumn('wood_transaction_details', 'wood_stock_id')) {
                $table->foreignId('wood_stock_id')
                      ->after('wood_transaction_id')
                      ->constrained('wood_stocks')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wood_transaction_details', function (Blueprint $table) {
            $table->dropForeign(['wood_stock_id']);
            $table->dropColumn('wood_stock_id');
        });
    }
};
