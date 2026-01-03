<?php

namespace Database\Factories;

use App\Models\ContractItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractItemFactory extends Factory
{
    protected $model = ContractItem::class;

    public function definition()
    {
        $quantity = $this->faker->randomNumber(2);
        $unitPrice = $this->faker->randomNumber(2);
        $weight = $this->faker->randomNumber(2);
        $machine_count = $this->faker->randomNumber(2);

        return [
            'description' => $this->faker->sentence(3),
            'size' => $this->faker->randomElement(['Small', 'Medium', 'Large']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'weight' => $weight,
            'machine_count' => $machine_count,
        ];
    }
}
