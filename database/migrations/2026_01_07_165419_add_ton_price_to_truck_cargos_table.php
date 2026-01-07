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
        Schema::table('truck_cargos', function (Blueprint $table) {
            $table->double('ton_price')->nullable()->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('truck_cargos', function (Blueprint $table) {
            $table->dropColumn('ton_price');
        });
    }
};
