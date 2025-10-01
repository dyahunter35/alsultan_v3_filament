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
        Schema::create('truck_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\Truck::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('note')->nullable();
            $table->date('issuance_date')->comment('تاريخ الاصدار')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_docs');
    }
};
