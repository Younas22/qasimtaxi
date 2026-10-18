<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Customer;
use App\Models\Review;
use Illuminate\Database\Seeder;

class CustomerActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::inRandomOrder()->limit(6)->get();
        $cities = City::inRandomOrder()->limit(3)->get();

        if ($customers->isEmpty()) {
            return;
        }

        foreach ($customers as $index => $customer) {
            // Saved addresses
            if ($cities->isNotEmpty() && $customer->addresses()->count() === 0) {
                $customer->addresses()->create([
                    'label' => 'Home',
                    'address_line' => fake()->streetAddress(),
                    'city_id' => $cities->random()->id,
                    'is_default' => true,
                ]);

                $customer->addresses()->create([
                    'label' => 'Work',
                    'address_line' => fake()->streetAddress(),
                    'city_id' => $cities->random()->id,
                    'is_default' => false,
                ]);
            }

            // Wallet ledger
            if ($customer->walletTransactions()->count() === 0) {
                $customer->creditWallet(100, 'Welcome bonus');
                $customer->creditWallet(fake()->randomFloat(2, 20, 80), 'Referral reward');

                if ($index % 2 === 0) {
                    $customer->debitWallet(fake()->randomFloat(2, 10, 40), 'Used for booking payment');
                }
            }

            // Loyalty ledger
            if ($customer->loyaltyTransactions()->count() === 0) {
                $customer->earnLoyaltyPoints(50, 'Sign-up bonus');
                $customer->earnLoyaltyPoints(fake()->numberBetween(10, 30), 'Completed booking');

                if ($index % 3 === 0) {
                    $customer->redeemLoyaltyPoints(20, 'Redeemed for discount');
                }
            }

        }

        // Reviews: seed for every completed booking (not just the random wallet/loyalty
        // sample above), so the moderation UI always has real demo content regardless
        // of how the random booking statuses landed.
        $completedBookings = \App\Models\Booking::where('status', 'completed')->with('customer')->get();

        foreach ($completedBookings as $booking) {
            if (! $booking->customer) {
                continue;
            }

            Review::firstOrCreate(
                ['customer_id' => $booking->customer_id, 'booking_id' => $booking->id],
                [
                    'driver_id' => $booking->driver_id,
                    'vehicle_id' => $booking->vehicle_id,
                    'rating' => fake()->numberBetween(3, 5),
                    'comment' => fake()->randomElement([
                        'Excellent service, the driver was punctual and professional.',
                        'Very comfortable ride, would book again.',
                        'Great experience overall, the vehicle was spotless.',
                        'Smooth booking process and a friendly driver.',
                    ]),
                    'status' => fake()->randomElement(['approved', 'approved', 'approved', 'pending', 'rejected']),
                ]
            );
        }
    }
}
