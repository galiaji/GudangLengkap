<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wood_transaction_details', function (Blueprint $table) {

            $table->foreignId('wood_type_id')
                ->nullable()
                ->after('wood_stock_id')
                ->constrained('wood_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wood_transaction_details', function (Blueprint $table) {

            $table->dropForeign(['wood_type_id']);
            $table->dropColumn('wood_type_id');
        });
    }
};