<?php

namespace Database\Factories;

use App\Enums\ExpenseType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'permanent' => $this->faker->randomElement([
                ExpenseType::SALE->value,
                ExpenseType::GOVERNMENT_FEES->value,
                ExpenseType::DEBTORS->value,
                ExpenseType::CUSTOMS->value,
                ExpenseType::TAX->value,
            ]),
        ];
    }
}
