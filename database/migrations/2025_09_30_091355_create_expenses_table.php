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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('beneficiary');

            $table->nullableMorphs('payer');

            $table->foreignId('representative_id')
                ->nullable()
                ->comment('المندوب')
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();



            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->double('amount');              // الكمية / المبلغ
            $table->double('unit_price');          // سعر الوحدة
            $table->double('total_amount');        // الإجمالي
            $table->double('remaining_amount');    // الباقي غير مدفوع

            //$table->string('expense_type');        // نوع المصروف
            $table->string('payment_method');      // وسيلة الدفع
            $table->string('payment_reference')->nullable(); // رقم الإشعار/الإيصال
            $table->boolean('is_paid')->default(false); // حالة الدفع

            $table->text('notes')->nullable();     // ملاحظات

            $table->foreignId('created_by')
                ->comment('المستخدم الذي أنشأ العملية')
                ->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
