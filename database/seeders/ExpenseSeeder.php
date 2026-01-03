<?php

namespace Database\Seeders;

use App\Enums\PaymentOptions;
use App\Models\Customer;
use App\Models\ExpenseType as ModelsExpenseType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $customers = Customer::all();
        $expenseType = ModelsExpenseType::all();

        if ($users->isEmpty() || $customers->isEmpty()) {
            $this->command->warn('⚠️ ما في بيانات كفاية في users أو customers لإضافة المصروفات.');

            return;
        }

        // نحدد عدد المصروفات العشوائية
        $count = 10;

        for ($i = 0; $i < $count; $i++) {
            $beneficiary = $i % 2 === 0 ? $customers->random() : $users->random();
            $payer = $i % 2 === 0 ? $customers->random() : $users->random();
            $expenseType1 = $expenseType->random();
            $paymentMethod = collect(PaymentOptions::cases())->random();

            $amount = fake()->numberBetween(1, 10);
            $price = fake()->randomFloat(2, 50, 500);
            $total = $amount * $price;
            DB::table('expenses')->insert([
                'beneficiary_type' => get_class($beneficiary),
                'beneficiary_id' => $beneficiary->id,
                'payer_type' => get_class($payer),
                'payer_id' => $payer->id,
                'representative_id' => $users->random()->id,
                'branch_id' => 1,
                'amount' => $amount,
                'unit_price' => $price,
                'total_amount' => $total,
                'remaining_amount' => fake()->randomFloat(2, 0, $total),
                'expense_type_id' => $expenseType1->id,
                'payment_method' => $paymentMethod->value,
                'payment_reference' => strtoupper(fake()->bothify('PAY-###??')),
                'is_paid' => fake()->boolean(70),
                'notes' => fake()->sentence(),
                'created_by' => $users->first()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ تمت إضافة المصروفات بنجاح مع وسائل الدفع وأنواع المصروفات!');
    }
}
