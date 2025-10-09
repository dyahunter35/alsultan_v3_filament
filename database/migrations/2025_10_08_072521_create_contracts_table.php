
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->string('title')->default('Service Agreement');
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('reference_no')->unique();

            $table->date('effective_date')->nullable();
            $table->integer('duration_months')->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();

            $table->text('scope_of_services')->nullable();
            $table->text('confidentiality_clause')->nullable();
            $table->text('termination_clause')->nullable();
            $table->string('governing_law')->nullable();

            $table->enum('status', ['active', 'completed', 'terminated', 'pending'])->default('active');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
