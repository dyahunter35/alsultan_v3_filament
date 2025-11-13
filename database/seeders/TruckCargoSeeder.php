<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Truck;
use App\Models\Product;
use App\Models\TruckCargo;
use App\Enums\TruckType;

class TruckCargoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Ensure there are products and trucks
        if (Product::count() === 0) {
            Product::factory()->count(20)->create();
        }

        if (Truck::count() === 0) {
            Truck::factory()->count(10)->create();
        }

        $products = Product::all();
        $trucks = Truck::all();

        foreach ($trucks as $truck) {
            $itemsCount = rand(1, 6);

            for ($i = 0; $i < $itemsCount; $i++) {
                $product = $products->random();

                $unitQuantity = $faker->randomFloat(2, 0.5, 100);
                $quantity = max(1, (int) ceil($unitQuantity / max(0.1, $faker->randomFloat(2, 0.5, 5))));
                $realQuantity = $quantity + $faker->numberBetween(-1, 2);
                $weight = $faker->randomFloat(2, 0.1, 500);
                $unitPrice = $faker->randomFloat(2, 1, 2000);

                TruckCargo::create([
                    'truck_id' => $truck->id,
                    'type' => $truck->type ?? TruckType::Outer->value,
                    'size' => $faker->optional()->randomElement(['S', 'M', 'L', 'XL']),
                    'product_id' => $product->id,
                    'unit_quantity' => $unitQuantity,
                    'quantity' => $quantity,
                    'real_quantity' => max(0, $realQuantity),
                    'weight' => $weight,
                    'unit_price' => $unitPrice,
                    'note' => $faker->optional()->sentence(),
                ]);
            }
        }
    }
}
