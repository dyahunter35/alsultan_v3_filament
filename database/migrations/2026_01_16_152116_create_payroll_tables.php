<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete(); // Delegate/Admin
            $table->decimal('amount', 10, 2);
            $table->text('notes')->nullable();
            $table->boolean('is_recovered')->default(false);
            $table->timestamps();
        });

        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete(); // Delegate/Admin
            $table->date('payment_date');
            $table->string('for_month'); // YYYY-MM
            
            // Components
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('transportation_allowance', 10, 2)->default(0);
            $table->decimal('housing_allowance', 10, 2)->default(0);
            
            // Work Hours Logic
            $table->integer('work_hours')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            
            // Adjustments
            $table->decimal('advances_deducted', 10, 2)->default(0);
            $table->decimal('penalties', 10, 2)->default(0);
            $table->decimal('incentives', 10, 2)->default(0);
            
            $table->decimal('net_pay', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('base_salary', 10, 2)->nullable()->after('balance');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('base_salary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('salary_advances');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'hourly_rate']);
        });
    }
};
