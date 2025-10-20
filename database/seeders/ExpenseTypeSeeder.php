<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\ExpenseType;
use App\Enums\Group;
use Illuminate\Support\Facades\DB;
use ReflectionEnum;

class ExpenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $reflection = new ReflectionEnum(ExpenseType::class);

        foreach ($reflection->getCases() as $case) {
            $attributes = $case->getAttributes(Group::class);

            $group = null;
            if (!empty($attributes)) {
                $group = $attributes[0]->getArguments()[0] ?? null;
            }

            $enum = $case->getValue();

            DB::table('expense_types')->updateOrInsert(
                ['key' => $enum->value],
                [
                    'label' => $enum->getLabel(),
                    'group' => $group,
                    'icon' => $enum->getIcon(),
                    'color' => $enum->getColor(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
