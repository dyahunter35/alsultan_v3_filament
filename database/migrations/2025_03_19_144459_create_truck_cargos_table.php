<?php

use App\Enums\TruckType;
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
        Schema::create('truck_cargos', function (Blueprint $table) {

            $table->id();

            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();

            $table->enum('type', TruckType::getKeys())->comment("١ -خارجي , ٢ - داخلي");

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->double('quantity');

            $table->double('real_quantity')->nullable();

            $table->double('weight')->nullable();

            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_cargos');
    }
};
