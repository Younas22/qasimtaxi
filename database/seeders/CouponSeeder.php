<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'description' => '10% off your first ride',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount' => 25,
                'usage_limit' => null,
                'expires_at' => now()->addMonths(3),
            ],
            [
                'code' => 'AIRPORT20',
                'description' => '$20 off airport transfers',
                'discount_type' => 'fixed',
                'discount_value' => 20,
                'min_fare' => 60,
                'usage_limit' => 500,
                'expires_at' => now()->addMonth(),
            ],
            [
                'code' => 'WEEKEND15',
                'description' => '15% off weekend rides',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_discount' => 40,
                'expires_at' => now()->addWeeks(6),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::firstOrCreate(['code' => $coupon['code']], $coupon + ['is_active' => true, 'used_count' => 0]);
        }
    }
}
