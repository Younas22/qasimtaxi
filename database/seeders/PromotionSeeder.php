<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = [
            [
                'title' => 'Fly in Style — 20% Off Airport Transfers',
                'subtitle' => 'Book your next airport pickup and save on every ride.',
                'badge_text' => 'Limited Time',
                'link_url' => '/services',
                'sort_order' => 1,
            ],
            [
                'title' => 'Refer a Friend, Earn Wallet Credit',
                'subtitle' => 'Give $10, get $10 when your friend completes their first ride.',
                'badge_text' => 'New',
                'link_url' => null,
                'sort_order' => 2,
            ],
            [
                'title' => 'Weekend Getaway Rides',
                'subtitle' => 'Save 15% on hourly bookings every Friday to Sunday.',
                'badge_text' => 'Weekends Only',
                'link_url' => '/services',
                'sort_order' => 3,
            ],
        ];

        foreach ($promotions as $promotion) {
            Promotion::firstOrCreate(['title' => $promotion['title']], $promotion + ['is_active' => true]);
        }
    }
}
