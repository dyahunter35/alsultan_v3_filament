<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('expense_types'))
            Schema::create('expense_types', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique(); // اسم القيمة من الـ enum (مثلاً sale, rent)
                $table->string('label');         // الاسم العربي
                $table->string('group')->nullable(); // القروب من الـ attribute
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->timestamps();
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_types');
    }
};
