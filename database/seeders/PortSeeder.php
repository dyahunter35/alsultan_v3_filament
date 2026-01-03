<?php

namespace Database\Seeders;

use App\Models\Port;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        // Port::factory()->count(5)->create();
        DB::table('ports')->insert([
            [
                'name' => 'أرقين',
                'description' => 'معبر بري يربط السودان بمصر، يُستخدم لنقل البضائع والمسافرين عبر الصحراء.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'سواكن',
                'description' => 'ميناء بحري رئيسي على البحر الأحمر يُستخدم لتصدير واستيراد السلع ونقل الركاب.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'حلفا',
                'description' => 'معبر حدودي شمال السودان يستخدم في التجارة البرية بين السودان ومصر.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
