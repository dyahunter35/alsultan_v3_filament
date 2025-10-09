<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contract;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء 10 عقود مع العناصر والملفات
        Contract::factory()->count(2)->create();
    }
}
