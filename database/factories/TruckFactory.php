<?php

namespace Database\Factories;

use App\Enums\Country;
use App\Enums\TruckState;
use App\Enums\TruckType;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Port;
use App\Models\Truck;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TruckFactory extends Factory
{
    protected $model = Truck::class;

    public function definition(): array
    {
        $packDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $arriveDate = (clone $packDate)->modify('+'.$this->faker->numberBetween(0, 10).' days');

        $tripDays = $packDate->diff($arriveDate)->days + 1;
        $agreedDuration = $this->faker->numberBetween(1, 7);
        $diffTrip = max($tripDays - $agreedDuration, 0);
        $delayDayValue = $this->faker->numberBetween(0, 500);
        $delayValue = $diffTrip * $delayDayValue;
        $truckFare = $this->faker->numberBetween(500, 20000);
        $totalAmount = $delayValue + $truckFare;

        return [
            'driver_name' => $this->faker->name(),
            'driver_phone' => $this->faker->phoneNumber(),
            'car_number' => $this->faker->bothify('??-####'),
            'pack_date' => Carbon::instance($packDate)->toDateString(),
            'arrive_date' => Carbon::instance($arriveDate)->toDateString(),

            'company_id' => Company::inRandomOrder()->where('type', 'company')->first()?->id,
            'contractor_id' => Company::inRandomOrder()->where('type', 'contractor')->first()?->id,
            'company' => $this->faker->company(),

            // polymorphic from (use Port here)
            'from_type' => Port::class,
            'from_id' => Port::inRandomOrder()->first()?->id,

            'branch_to' => Branch::inRandomOrder()->first()?->id,

            'truck_status' => TruckState::OnWay->value ?? 'onway',
            'type' => TruckType::Outer->value ?? 'outer',
            'is_converted' => $this->faker->boolean(10),
            'note' => $this->faker->optional()->sentence(),

            'category_id' => Category::inRandomOrder()->first()?->id,
            'country' => $this->faker->randomElement(Country::getKeys()),
            'city' => $this->faker->city(),
            'truck_model' => $this->faker->word(),

            'trip_days' => $tripDays,
            'diff_trip' => $diffTrip,
            'agreed_duration' => $agreedDuration,
            'delay_day_value' => $delayDayValue,
            'truck_fare' => $truckFare,
            'delay_value' => $delayValue,
            'total_amount' => $totalAmount,
        ];
    }
}
