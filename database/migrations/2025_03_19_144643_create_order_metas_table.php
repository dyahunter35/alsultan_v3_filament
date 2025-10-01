<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('order_metas')) {

            Schema::create('order_metas', function (Blueprint $table) {
                $table->id();

                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

                $table->string('key');
                $table->json('value')->nullable();
                $table->string('type')->default('text')->nullable();
                $table->string('group')->default('general')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_metas');
    }
};
