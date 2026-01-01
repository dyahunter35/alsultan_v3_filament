<?php

use App\Models\Currency;
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
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignIdFor(Currency::class)->nullable()
                ->after('email')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->dropColumn('default_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // 1. حذف قيد المفتاح الأجنبي أولاً (نمرر اسم العمود في مصفوفة ليتعرف Laravel على القيد تلقائياً)
            $table->dropForeign(['currency_id']);

            // 2. حذف العمود نفسه (سيقوم بحذف الفهرس المرتبط به تلقائياً)
            $table->dropColumn('currency_id');

            $table->string('default_currency')->nullable();
        });
    }
};
