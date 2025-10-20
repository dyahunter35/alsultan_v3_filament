<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_type_id')
                ->nullable()
                ->constrained('expense_types')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('custom_expense_type')->nullable()->comment('نوع منصرف مخصص من array predefined');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_type_id');
            $table->dropColumn('custom_expense_type');
        });
    }
};
