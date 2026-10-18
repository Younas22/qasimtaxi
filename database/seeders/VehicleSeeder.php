<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            'sedan' => [
                'name' => 'Mercedes-Benz E-Class',
                'brand' => 'Mercedes-Benz', 'model' => 'E-Class', 'year' => 2024,
                'plate_number' => 'SED-1001', 'seats' => 4, 'luggage' => 3,
                'transmission' => 'automatic', 'fuel_type' => 'petrol',
                'description' => 'Elegant executive sedan, perfect for business travel and airport transfers.',
                'base_fare' => 45, 'price_per_km' => 2.5, 'price_per_hour' => 40, 'airport_price' => 65, 'night_charges' => 15,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => false, 'has_ac' => true,
            ],
            'suv' => [
                'name' => 'Cadillac Escalade',
                'brand' => 'Cadillac', 'model' => 'Escalade', 'year' => 2024,
                'plate_number' => 'SUV-1002', 'seats' => 6, 'luggage' => 5,
                'transmission' => 'automatic', 'fuel_type' => 'petrol',
                'description' => 'Spacious and powerful SUV for groups and families with extra luggage.',
                'base_fare' => 60, 'price_per_km' => 3, 'price_per_hour' => 55, 'airport_price' => 85, 'night_charges' => 20,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => true, 'has_ac' => true,
            ],
            'limousine' => [
                'name' => 'Lincoln Stretch Limousine',
                'brand' => 'Lincoln', 'model' => 'Town Car Stretch', 'year' => 2023,
                'plate_number' => 'LIM-1003', 'seats' => 8, 'luggage' => 4,
                'transmission' => 'automatic', 'fuel_type' => 'petrol',
                'description' => 'Classic stretch limousine for weddings, proms, and VIP occasions.',
                'base_fare' => 90, 'price_per_km' => 4, 'price_per_hour' => 95, 'airport_price' => 120, 'night_charges' => 35,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => false, 'has_ac' => true,
            ],
            'van' => [
                'name' => 'Mercedes-Benz Sprinter',
                'brand' => 'Mercedes-Benz', 'model' => 'Sprinter', 'year' => 2023,
                'plate_number' => 'VAN-1004', 'seats' => 12, 'luggage' => 8,
                'transmission' => 'automatic', 'fuel_type' => 'diesel',
                'description' => 'Roomy passenger van ideal for group transport and airport shuttles.',
                'base_fare' => 55, 'price_per_km' => 2.75, 'price_per_hour' => 50, 'airport_price' => 75, 'night_charges' => 18,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => false, 'has_baby_seat' => true, 'has_ac' => true,
            ],
            'luxury' => [
                'name' => 'Rolls-Royce Ghost',
                'brand' => 'Rolls-Royce', 'model' => 'Ghost', 'year' => 2024,
                'plate_number' => 'LUX-1005', 'seats' => 4, 'luggage' => 2,
                'transmission' => 'automatic', 'fuel_type' => 'petrol',
                'description' => 'The ultimate in VIP and executive travel — premium comfort throughout.',
                'base_fare' => 150, 'price_per_km' => 6, 'price_per_hour' => 140, 'airport_price' => 200, 'night_charges' => 50,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => false, 'has_ac' => true,
            ],
            'electric' => [
                'name' => 'Tesla Model S',
                'brand' => 'Tesla', 'model' => 'Model S', 'year' => 2024,
                'plate_number' => 'EV-1006', 'seats' => 4, 'luggage' => 3,
                'transmission' => 'automatic', 'fuel_type' => 'electric',
                'description' => 'Silent, eco-friendly, and packed with premium technology.',
                'base_fare' => 55, 'price_per_km' => 2.75, 'price_per_hour' => 48, 'airport_price' => 75, 'night_charges' => 18,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => false, 'has_ac' => true,
            ],
        ];

        foreach ($vehicles as $categorySlug => $attributes) {
            $category = VehicleCategory::where('slug', $categorySlug)->first();

            if (! $category) {
                continue;
            }

            Vehicle::firstOrCreate(
                ['plate_number' => $attributes['plate_number']],
                $attributes + ['vehicle_category_id' => $category->id, 'status' => true]
            );
        }
    }
}
