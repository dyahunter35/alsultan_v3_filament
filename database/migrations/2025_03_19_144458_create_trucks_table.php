<?php

use App\Enums\TruckType;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SebastianBergmann\Type\TrueType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name')->comment("اسم السائق");
            $table->string('driver_phone')->comment("رقم الهاتف");
            $table->string('car_number')->comment("رقم العربة");
            $table->date('pack_date')->comment("تاريخ التعبئة");

            $table->foreignIdFor(\App\Models\Company::class)
                ->nullable()
                ->cascadeOnDelete();
            $table->string('company')->comment('من الشركة او المخزن')->nullable();

            $table->nullableMorphs('from', 'from');

            $table->foreignIdFor(Branch::class, 'branch_to')->comment("الفرع");

            $table->date('arrive_date')->nullable()->comment("تاريخ الوصول");
            $table->tinyInteger('truck_status')->comment("حاله الشحنة");
            $table->enum('type',TruckType::getKeys())->comment("١ -خارجي , ٢ - داخلي");
            $table->boolean('is_converted')->comment("1 -تم التحويل , 0 - لم يتم")->default(0);
            $table->string('note')->nullable();

            $table->foreignIdFor(Category::class);
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('truck_model')->nullable();

            // Add additional fields to the trucks table
            $table->integer('trip_days')->comment('ايام الرحلة')->nullable();
            $table->integer('diff_trip')->comment('الفرق بين الايام')->nullable();
            $table->integer('agreed_duration')->comment('الايام المتفق عليها')->nullable();
            $table->integer('delay_day_value')->comment("قيمة يوم التاخير")->nullable();
            $table->integer('truck_fare')->comment("اجرة الشاحنة - النولون")->nullable();
            $table->integer('delay_value')->comment("قيمة الععطلات")->nullable();
            $table->integer('total_amount')->comment("المجموع الكلي")->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};
