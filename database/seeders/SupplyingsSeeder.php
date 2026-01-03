<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplyingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('supplyings')->insert([
            0 => [
                'customer_id' => 43,
                'representative_id' => 1,
                'is_completed' => 1,
                'payment_method' => 'bok',
                'paid_amount' => 0.0,
                'statement' => '67676687',
                'payment_reference' => '67767',
                'total_amount' => 4000.0,
                'created_by' => 1,
                'created_at' => '2025-10-18 00:00:00',
                'updated_at' => '2025-10-24 08:47:25',
            ],
            1 => [
                'customer_id' => 5,
                'representative_id' => 1,
                'is_completed' => 1,
                'payment_method' => 'bok',
                'paid_amount' => 70000.0,
                'statement' => '67676687',
                'payment_reference' => '56656',
                'total_amount' => 80000.0,
                'created_by' => null,
                'created_at' => null,
                'updated_at' => '2025-10-19 08:45:15',
            ],
        ]);
    }
}
