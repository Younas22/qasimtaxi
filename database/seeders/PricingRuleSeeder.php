<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PricingRule::global();

        $tiers = [
            'Sedan' => ['base_fare' => 20, 'km_fare' => 2.00, 'hour_fare' => 35, 'airport_surcharge' => 15],
            'SUV' => ['base_fare' => 30, 'km_fare' => 2.75, 'hour_fare' => 45, 'airport_surcharge' => 20],
            'Limousine' => ['base_fare' => 60, 'km_fare' => 4.50, 'hour_fare' => 80, 'airport_surcharge' => 40],
            'Van' => ['base_fare' => 35, 'km_fare' => 2.50, 'hour_fare' => 50, 'airport_surcharge' => 20],
            'Luxury' => ['base_fare' => 75, 'km_fare' => 5.00, 'hour_fare' => 95, 'airport_surcharge' => 50],
            'Electric' => ['base_fare' => 25, 'km_fare' => 2.25, 'hour_fare' => 40, 'airport_surcharge' => 18],
        ];

        foreach (VehicleCategory::all() as $category) {
            $tier = $tiers[$category->name] ?? ['base_fare' => 25, 'km_fare' => 2.50, 'hour_fare' => 40, 'airport_surcharge' => 20];

            PricingRule::updateOrCreate(
                ['vehicle_category_id' => $category->id],
                array_merge($tier, [
                    'label' => $category->name,
                    'waiting_charge_per_minute' => 0.50,
                    'free_waiting_minutes' => 10,
                    'night_charge' => round($tier['base_fare'] * 0.3, 2),
                    'night_start_time' => '22:00:00',
                    'night_end_time' => '06:00:00',
                    'weekend_charge' => round($tier['base_fare'] * 0.2, 2),
                    'weekend_days' => ['Saturday', 'Sunday'],
                    'toll_charge' => 8,
                    'service_fee' => 5,
                    'is_active' => true,
                ])
            );
        }
    }
}
