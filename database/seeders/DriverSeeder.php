<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'James Anderson', 'email' => 'james.anderson@limoschedule.test', 'phone' => '+1 555-0201',
                'address' => '221 Park Avenue, New York, NY', 'license_number' => 'DL-10023',
                'passport_number' => 'P1002233', 'national_id' => '100-22-3344',
                'commission_rate' => 20, 'is_online' => true, 'is_available' => true, 'vehicle_plate' => 'SED-1001',
            ],
            [
                'name' => 'Michael Carter', 'email' => 'michael.carter@limoschedule.test', 'phone' => '+1 555-0202',
                'address' => '88 Sunset Blvd, Los Angeles, CA', 'license_number' => 'DL-10024',
                'passport_number' => 'P1002234', 'national_id' => '100-22-3345',
                'commission_rate' => 22, 'is_online' => true, 'is_available' => false, 'vehicle_plate' => 'SUV-1002',
            ],
            [
                'name' => 'David Thompson', 'email' => 'david.thompson@limoschedule.test', 'phone' => '+1 555-0203',
                'address' => '5 Fifth Avenue, New York, NY', 'license_number' => 'DL-10025',
                'passport_number' => 'P1002235', 'national_id' => '100-22-3346',
                'commission_rate' => 25, 'is_online' => false, 'is_available' => true, 'vehicle_plate' => 'LIM-1003',
            ],
            [
                'name' => 'Robert Wilson', 'email' => 'robert.wilson@limoschedule.test', 'phone' => '+1 555-0204',
                'address' => '12 Ocean Drive, Miami, FL', 'license_number' => 'DL-10026',
                'passport_number' => 'P1002236', 'national_id' => '100-22-3347',
                'commission_rate' => 18, 'is_online' => true, 'is_available' => true, 'vehicle_plate' => 'VAN-1004',
            ],
            [
                'name' => 'William Harris', 'email' => 'william.harris@limoschedule.test', 'phone' => '+1 555-0205',
                'address' => '9 Rodeo Drive, Beverly Hills, CA', 'license_number' => 'DL-10027',
                'passport_number' => 'P1002237', 'national_id' => '100-22-3348',
                'commission_rate' => 15, 'is_online' => false, 'is_available' => false, 'vehicle_plate' => 'LUX-1005',
            ],
            [
                'name' => 'Daniel Martinez', 'email' => 'daniel.martinez@limoschedule.test', 'phone' => '+1 555-0206',
                'address' => '77 Market Street, San Francisco, CA', 'license_number' => 'DL-10028',
                'passport_number' => 'P1002238', 'national_id' => '100-22-3349',
                'commission_rate' => 20, 'is_online' => true, 'is_available' => true, 'vehicle_plate' => 'EV-1006',
            ],
        ];

        foreach ($drivers as $attributes) {
            $vehicle = Vehicle::where('plate_number', $attributes['vehicle_plate'])->first();
            unset($attributes['vehicle_plate']);

            Driver::firstOrCreate(
                ['email' => $attributes['email']],
                $attributes + ['vehicle_id' => $vehicle?->id, 'status' => true]
            );
        }
    }
}
