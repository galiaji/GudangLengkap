<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE wood_transaction_details d
            JOIN wood_stocks s
                ON d.wood_stock_id = s.id
            SET d.wood_type_id = s.wood_type_id
        ");
    }

    public function down(): void
    {
        DB::table('wood_transaction_details')
            ->update([
                'wood_type_id' => null
            ]);
    }
};