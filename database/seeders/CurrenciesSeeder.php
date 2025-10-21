<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('currencies')->insert(array(
            0 =>
            array(
                'name' => 'دولار أمريكي',
                'code' => 'USD',
                'symbol' => '$',
                'exchange_rate' => '512.000000',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
    }
}
