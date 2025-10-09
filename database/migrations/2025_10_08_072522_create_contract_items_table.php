<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->text('size');
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('machine_count', 10, 2)->default(0);
            $table->decimal('total_price', 15, 2)->storedAs('quantity * unit_price');
            $table->decimal('total_weight', 15, 2)->storedAs('quantity * weight');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_items');
    }
};
