<?php

namespace Database\Factories;

use App\Models\Port;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortFactory extends Factory
{
    protected $model = Port::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'description' => $this->faker->unique()->bothify('PORT-####'),
        ];
    }
}
