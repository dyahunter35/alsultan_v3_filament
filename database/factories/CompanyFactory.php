<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\CompanyType;
use App\Enums\CurrencyOption;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'location' => $this->faker->city(),
            'type' => $this->faker->randomElement(CompanyType::getKeys()), // تأكد من Enum
            'default_currency' => $this->faker->randomElement(CurrencyOption::getKeys()),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'partner_type' => $this->faker->randomElement(['client', 'supplier', 'partner']),
        ];
    }
}
