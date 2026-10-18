<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(array_keys(Booking::TYPES));
        $pickupDateTime = fake()->dateTimeBetween('-1 month', '+1 week');

        return [
            'customer_id' => Customer::factory(),
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'type' => $type,
            'pickup_location' => fake()->streetAddress(),
            'dropoff_location' => fake()->streetAddress(),
            'stops' => [],
            'pickup_datetime' => $pickupDateTime,
            'return_datetime' => $type === 'round_trip'
                ? fake()->dateTimeBetween($pickupDateTime, (clone $pickupDateTime)->modify('+5 days'))
                : null,
            'hours' => $type === 'hourly' ? fake()->numberBetween(2, 6) : null,
            'distance_km' => in_array($type, ['one_way', 'round_trip', 'corporate'], true)
                ? fake()->randomFloat(2, 5, 60)
                : null,
            'passengers' => fake()->numberBetween(1, 6),
            'luggage' => fake()->numberBetween(0, 4),
            'waiting_minutes' => fake()->boolean(40) ? fake()->numberBetween(0, 45) : 0,
            'has_toll' => fake()->boolean(25),
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            'fare_amount' => fake()->randomFloat(2, 45, 450),
            'status' => fake()->randomElement(array_keys(Booking::STATUSES)),
        ];
    }
}
