<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $total = 0;
        $discount = $this->faker->randomFloat(2, 0, 200);
        $shipping = $this->faker->randomFloat(2, 0, 50);
        $install = $this->faker->randomFloat(2, 0, 50);
        $paid = 0;

        return [
            'branch_id' => Branch::inRandomOrder()->first()?->id,
            'customer_id' => Customer::inRandomOrder()->first()?->id,
            'representative_id' => User::inRandomOrder()->first()?->id,
            'number' => strtoupper(Str::random(10)),
            'total_price' => null,
            'status' => 'new',
            'currency' => 'SDG',
            'total' => $total,
            'discount' => $discount,
            'shipping' => $shipping,
            'install' => $install,
            'paid' => $paid,
            'shipping_method' => null,
            'notes' => $this->faker->optional()->sentence(),
            'caused_by' => User::inRandomOrder()->first()?->id,
            'guest_customer' => null,
            'is_guest' => false,
        ];
    }
}
