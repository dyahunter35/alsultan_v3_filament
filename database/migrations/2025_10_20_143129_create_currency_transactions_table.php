<?php

use App\Enums\CurrencyType;
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
        Schema::create('currency_transactions', function (Blueprint $table) {
            $table->id();

            // العملية الأساسية
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();

            // الجهة المدفوعة / المستلمة
            $table->nullableMorphs('payer'); // payer_id , payer_type

            // الجهة المرتبطة (Customer / Company / User)
            $table->nullableMorphs('party'); // party_id , party_type

            // القيم المالية
            $table->double('amount');
            $table->double('rate')->default(1);

            $table->double('total')->computed('amount * rate');

            // نوع المعاملة (إضافة / خصم)
            $table->enum('type', CurrencyType::getKeys())->default(CurrencyType::SEND);

            $table->string('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_transactions');
    }
};
