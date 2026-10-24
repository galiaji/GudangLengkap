<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wood_transaction_details', function (Blueprint $table) {

            $table->foreignId('wood_type_id')
                ->nullable()
                ->after('wood_stock_id')
                ->constrained('wood_types');
        });

        DB::statement("
            UPDATE wood_transaction_details d
            JOIN wood_stocks s
                ON d.wood_stock_id = s.id
            SET d.wood_type_id = s.wood_type_id
        ");

        Schema::table('wood_transaction_details', function (Blueprint $table) {

            $table->dropColumn('wood_type');
        });
    }

    public function down(): void
    {
        Schema::table('wood_transaction_details', function (Blueprint $table) {

            $table->string('wood_type')->nullable();

            $table->dropForeign(['wood_type_id']);
            $table->dropColumn('wood_type_id');
        });
    }
};