<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // If an Order factory exists, use it. Otherwise, print a message and skip.
        $factoryPath = database_path('factories/OrderFactory.php');

        if (file_exists($factoryPath)) {
            try {
                Order::factory()->count(20)->create();
                $this->command?->info('Created 20 orders using OrderFactory.');
            } catch (\Throwable $e) {
                $this->command?->error('Order seeding failed: ' . $e->getMessage());
            }

            return;
        }

        $this->command?->info('OrderFactory not found at ' . $factoryPath . '. Skipping Order seeding.');
        $this->command?->info('To enable order seeding, create a factory at database/factories/OrderFactory.php that returns a valid Order model.');
    }
}
