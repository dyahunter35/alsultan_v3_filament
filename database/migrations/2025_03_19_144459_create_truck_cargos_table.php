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

            $table->enum('type', TruckType::getKeys())->comment('local , outer');

            $table->string('size')->nullable()->comment('مقاسات المنتج');

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->double('unit_quantity')->comment('الكميات بالوحدة وليس الطرد');

            $table->double('quantity')->comment('الكميات بالطرد');

            $table->double('real_quantity')->nullable()->comment('الكمية الفعلية عند الاستلام');

            $table->double('weight')->nullable();

            $table->double('unit_price')->nullable();

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
