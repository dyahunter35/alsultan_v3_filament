<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition()
    {
        $faker = $this->faker;

        return [
            'title' => $faker->name(),
            'company_id' => Company::inRandomOrder()->first()?->id ?? Company::factory(),
            'effective_date' => $faker->date(),
            'duration_months' => $faker->numberBetween(6, 24),
            'total_amount' => $faker->randomFloat(2, 1000, 50000),
            'scope_of_services' => $faker->paragraph(3),
            'confidentiality_clause' => $faker->paragraph(2),
            'termination_clause' => $faker->paragraph(2),
            'governing_law' => 'Delaware, USA',
            'status' => $faker->randomElement(['active', 'completed', 'terminated', 'pending']),
            'notes' => $faker->optional()->text(50),
            'created_by' => User::inRandomOrder()->first()?->id ?? null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Contract $contract) {
            // إنشاء بعض العناصر للعقد
            $contract->items()->createMany(
                \App\Models\ContractItem::factory()->count(3)->make()->toArray()
            );

            // إنشاء بعض الملفات للعقد
            \App\Models\Document::factory()
                ->count(2)
                ->forDocumentable($contract)
                ->create();
        });
    }
}
