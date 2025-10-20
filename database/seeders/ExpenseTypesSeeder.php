<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseTypesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('expense_types')->insert(array(
            0 =>
            array(
                'id' => 1,
                'key' => 'sale',
                'label' => 'مبيعات',
                'group' => 'sale',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            1 =>
            array(
                'id' => 2,
                'key' => 'debtors',
                'label' => 'الدائنون',
                'group' => 'debtors',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            2 =>
            array(
                'id' => 3,
                'key' => 'salaries',
                'label' => 'مرتبات وأجور',
                'group' => 'currency',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            3 =>
            array(
                'id' => 4,
                'key' => 'advances',
                'label' => 'سلفيات',
                'group' => 'currency',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            4 =>
            array(
                'id' => 5,
                'key' => 'rep_transfer',
                'label' => 'تحويل مالي للمندوب',
                'group' => 'currency',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            5 =>
            array(
                'id' => 6,
                'key' => 'transport',
                'label' => 'منصرفات ترحيل',
                'group' => 'store',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            6 =>
            array(
                'id' => 7,
                'key' => 'food',
                'label' => 'منصرفات ميز',
                'group' => 'store',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            7 =>
            array(
                'id' => 8,
                'key' => 'carrier',
                'label' => 'عتالة',
                'group' => 'store',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            8 =>
            array(
                'id' => 9,
                'key' => 'rent',
                'label' => 'إيجارات',
                'group' => 'store',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            9 =>
            array(
                'id' => 10,
                'key' => 'customs',
                'label' => 'جمارك',
                'group' => 'customs',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            10 =>
            array(
                'id' => 11,
                'key' => 'certificates',
                'label' => 'شهادات وارد',
                'group' => 'certificates',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            11 =>
            array(
                'id' => 12,
                'key' => 'tax',
                'label' => 'ضرائب',
                'group' => 'tax',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
            12 =>
            array(
                'id' => 13,
                'key' => 'government_fees',
                'label' => 'رسوم حكومية',
                'group' => 'government_fees',
                'icon' => 'heroicon-m-user',
                'color' => 'success',
                'created_at' => '2025-10-20 09:51:21',
                'updated_at' => '2025-10-20 09:51:21',
            ),
        ));
    }
}
