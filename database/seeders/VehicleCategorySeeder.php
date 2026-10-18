<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Sedan', 'description' => 'Comfortable four-door sedans, ideal for business travel and airport transfers.'],
            ['name' => 'SUV', 'description' => 'Spacious sport utility vehicles for groups and extra luggage.'],
            ['name' => 'Limousine', 'description' => 'Classic stretch limousines for weddings, proms, and special occasions.'],
            ['name' => 'Van', 'description' => 'Passenger vans for group transport and larger parties.'],
            ['name' => 'Luxury', 'description' => 'Premium high-end vehicles for VIP and executive travel.'],
            ['name' => 'Electric', 'description' => 'Eco-friendly electric vehicles for a sustainable ride.'],
        ];

        foreach ($categories as $index => $category) {
            VehicleCategory::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($category['name'])],
                $category + ['sort_order' => $index + 1, 'is_active' => true]
            );
        }
    }
}
