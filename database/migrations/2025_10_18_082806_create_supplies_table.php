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
        Schema::create('supplyings', function (Blueprint $table) {
            $table->id();

            // المستخدم الذي قام بعملية التوريد (المسؤول)
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('المستخدم المنفذ');

            // المندوب الذي تم التوريد من خلاله
            $table->foreignId('representative_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('المندوب');

            // حالة العملية (0 = مكتملة، 1 = غير مكتملة)
            $table->boolean('is_completed')
                ->default(false)
                ->comment('0 = مكتملة، 1 = غير مكتملة');

            // نوع طريقة الدفع (نقدي، شيك، تحويل...)
            $table->string('payment_method')
                ->nullable()
                ->comment('وسيلة الدفع');

            // المبلغ المدفوع
            $table->double('paid_amount')
                ->comment('المبلغ المدفوع');

            // البيان أو الوصف النصي للعملية
            $table->string('statement')
                ->comment('وصف العملية');

            // رقم الإشعار أو الإيصال البنكي
            $table->string('payment_reference')
                ->nullable()
                ->comment('رقم الإشعار/الإيصال');

            // إجمالي المبلغ المستحق أو الإجمالي الكلي
            $table->double('total_amount')
                ->comment('الإجمالي');

            // المستخدم الذي تسبب أو أنشأ السجل (في حالة التتبع)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('المستخدم الذي أنشأ السجل');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplyings');
    }
};
