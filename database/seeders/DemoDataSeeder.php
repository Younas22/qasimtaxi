<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\BookingFareCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::factory(15)->create();
        $drivers = Driver::all()->isNotEmpty() ? Driver::all() : Driver::factory(8)->create();
        $vehicles = Vehicle::all()->isNotEmpty() ? Vehicle::all() : Vehicle::factory(6)->create();

        $calculator = app(BookingFareCalculator::class);
        $types = array_keys(Booking::TYPES);
        $statuses = array_keys(Booking::STATUSES);

        for ($i = 0; $i < 25; $i++) {
            $vehicle = $vehicles->random();
            $type = fake()->randomElement($types);
            $pickupDateTime = Carbon::parse(fake()->dateTimeBetween('-1 month', '+1 week'));
            $distanceKm = in_array($type, ['one_way', 'round_trip', 'corporate'], true)
                ? fake()->randomFloat(2, 5, 60)
                : null;
            $hours = $type === 'hourly' ? fake()->numberBetween(2, 6) : null;
            $waitingMinutes = fake()->boolean(40) ? fake()->numberBetween(0, 45) : 0;
            $hasToll = fake()->boolean(25);

            $breakdown = $calculator->breakdown($vehicle, $type, $distanceKm, $hours, $pickupDateTime, $waitingMinutes, $hasToll);
            $status = fake()->randomElement($statuses);
            $paymentStatus = match ($status) {
                'completed' => fake()->randomElement(['paid', 'paid', 'paid', 'paid', 'refunded', 'pending']),
                'cancelled' => fake()->randomElement(['refunded', 'pending']),
                default => 'pending',
            };

            Booking::create([
                'customer_id' => $customers->random()->id,
                'driver_id' => $drivers->random()->id,
                'vehicle_id' => $vehicle->id,
                'type' => $type,
                'pickup_location' => fake()->streetAddress(),
                'dropoff_location' => fake()->streetAddress(),
                'stops' => [],
                'pickup_datetime' => $pickupDateTime,
                'return_datetime' => $type === 'round_trip' ? (clone $pickupDateTime)->addDays(fake()->numberBetween(1, 5)) : null,
                'hours' => $hours,
                'distance_km' => $distanceKm,
                'passengers' => fake()->numberBetween(1, 6),
                'luggage' => fake()->numberBetween(0, 4),
                'waiting_minutes' => $waitingMinutes,
                'has_toll' => $hasToll,
                'notes' => fake()->boolean(30) ? fake()->sentence() : null,
                'fare_amount' => $breakdown['total'],
                'fare_breakdown' => $breakdown,
                'status' => $status,
                'payment_status' => $paymentStatus,
            ]);
        }
    }
}
