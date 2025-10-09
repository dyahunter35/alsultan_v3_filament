<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $price = $this->faker->randomFloat(2, 5, 1500);
        $cost = $this->faker->randomFloat(2, 1, $price);

        return [
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'sku' => strtoupper(Str::random(8)),
            'barcode' => $this->faker->ean13(),
            'description' => $this->faker->optional()->paragraph(),
            'qty' => $this->faker->numberBetween(0, 1000),

            'low_stock_notified_at' => null,
            'security_stock' => $this->faker->numberBetween(0, 50),
            'is_visible' => $this->faker->boolean(80),
            'old_price' => $this->faker->optional()->randomFloat(2, $price + 1, $price + 200),
            'price' => $price,
            'cost' => $cost,
        ];
    }
}
