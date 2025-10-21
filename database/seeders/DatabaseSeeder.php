<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Unit;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            ShieldSeeder::class,
            UserSeeder::class,
            BranchSeeder::class,
            CompanySeeder::class,
            ContractSeeder::class,
            CustomerSeeder::class,
            PortSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            TruckSeeder::class,
            CurrenciesSeeder::class,
            ExpenseTypesSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
