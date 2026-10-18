<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'license_number' => strtoupper(fake()->bothify('DL-#####')),
            'passport_number' => strtoupper(fake()->bothify('P########')),
            'national_id' => fake()->numerify('###-##-####'),
            'commission_rate' => fake()->randomFloat(2, 10, 30),
            'is_online' => fake()->boolean(50),
            'is_available' => fake()->boolean(70),
            'status' => true,
        ];
    }
}
