<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        //Unit::factory()->count(5)->create();

        DB::table('units')->insert([
            ['name' => 'طرد', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'جوال', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'كيلو', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'لفة', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'طن',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
