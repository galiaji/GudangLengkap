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
    Schema::table('wood_stocks', function ($table) {

        $table->foreignId('wood_type_id')
              ->nullable()
              ->after('id')
              ->constrained('wood_types');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wood_stocks', function (Blueprint $table) {
            $table->dropForeign(['wood_type_id']);
            $table->dropColumn('wood_type_id');
        });
    }
};
