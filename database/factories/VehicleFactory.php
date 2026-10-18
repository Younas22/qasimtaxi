<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brand = fake()->randomElement(['Mercedes-Benz', 'BMW', 'Cadillac', 'Rolls-Royce', 'Range Rover', 'Tesla']);
        $model = fake()->randomElement(['S-Class', '7 Series', 'Escalade', 'Ghost', 'Vogue', 'Model S']);

        return [
            'vehicle_category_id' => VehicleCategory::factory(),
            'name' => "{$brand} {$model}",
            'brand' => $brand,
            'model' => $model,
            'year' => fake()->numberBetween(2020, now()->year),
            'plate_number' => strtoupper(fake()->unique()->bothify('???-####')),
            'seats' => fake()->numberBetween(2, 7),
            'luggage' => fake()->numberBetween(1, 5),
            'transmission' => fake()->randomElement(['automatic', 'manual']),
            'fuel_type' => fake()->randomElement(['petrol', 'diesel', 'hybrid', 'electric']),
            'base_fare' => fake()->randomFloat(2, 30, 150),
            'price_per_km' => fake()->randomFloat(2, 1, 5),
            'price_per_hour' => fake()->randomFloat(2, 20, 80),
            'airport_price' => fake()->randomFloat(2, 40, 120),
            'night_charges' => fake()->randomFloat(2, 10, 40),
            'has_wifi' => fake()->boolean(70),
            'has_water' => fake()->boolean(80),
            'has_charger' => fake()->boolean(75),
            'has_baby_seat' => fake()->boolean(30),
            'has_ac' => true,
            'status' => true,
        ];
    }
}
