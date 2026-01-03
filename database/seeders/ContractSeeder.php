<?php

namespace Database\Seeders;

use App\Models\Contract;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء 10 عقود مع العناصر والملفات
        Contract::factory()->count(2)->create();
    }
}
