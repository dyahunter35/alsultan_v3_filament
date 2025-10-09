<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Truck;

class TruckSeeder extends Seeder
{
    public function run(): void
    {
        Truck::factory()->count(10)->create();
    }
}
