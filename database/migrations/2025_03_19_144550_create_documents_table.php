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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // علاقة polymorphic عشان نقدر نربط المستند بأي موديل (Truck, Contract, ...)
            $table->morphs('documentable'); // بيعمل عمودين: documentable_id, documentable_type

            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('note')->nullable();
            $table->string('file_type')->nullable();
            $table->date('issuance_date')->nullable()->comment('تاريخ الإصدار');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
