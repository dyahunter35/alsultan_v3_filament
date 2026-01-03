<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $qty = $this->faker->randomFloat(2, 1, 10);
        $price = $this->faker->randomFloat(2, 5, 1000);
        $subDiscount = $this->faker->randomFloat(2, 0, min(50, $price * 0.2));

        return [
            'sort' => 0,
            'product_id' => Product::inRandomOrder()->first()?->id,
            'order_id' => null,
            'qty' => $qty,
            'price' => $price,
            'sub_discount' => $subDiscount,
            'sub_total' => ($price - $subDiscount) * $qty,
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
