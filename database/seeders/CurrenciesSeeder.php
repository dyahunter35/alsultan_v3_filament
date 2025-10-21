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
            1 => [
                'name' => 'جنيه سوداني',
                'code' => 'SDG',
                'symbol' => 'ج.س',
                'exchange_rate' => '1.000000',
                'created_at' => NULL,
                'updated_at' => NULL,
            ],
            2 => [
                'name' => 'ريال سعودي',
                'code' => 'SAR',
                'symbol' => 'ر.س',
                'exchange_rate' => '136.000000',
                'created_at' => NULL,
                'updated_at' => NULL,
            ],
            3 => [
                'name' => 'درهم إماراتي',
                'code' => 'AED',
                'symbol' => 'د.إ',
                'exchange_rate' => '139.000000',
                'created_at' => NULL,
                'updated_at' => NULL,
            ],
            4 => [
                'name' => 'ريال قطري',
                'code' => 'QAR',
                'symbol' => 'ر.ق',
                'exchange_rate' => '140.000000',
                'created_at' => NULL,
                'updated_at' => NULL,
            ],

        ));
    }
}
