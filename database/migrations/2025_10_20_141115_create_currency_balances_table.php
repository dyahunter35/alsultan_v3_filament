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
        Schema::create('currency_balances', function (Blueprint $table) {
            $table->id();

            // المالك (عميل أو شركة أو مستخدم)
            $table->morphs('owner'); // => owner_id + owner_type

            // العملة المرتبطة
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();

            // الرصيد الحالي
            $table->decimal('amount', 15, 2)->default(0);

            // المجموع المحول بالجنيه مثلاً
            $table->decimal('total_in_sdg', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_balances');
    }
};
