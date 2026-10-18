<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::firstOrCreate(['id' => 1], [
            'company_name' => 'Limo Schedule',
            'tagline' => 'Premium Chauffeur Service',
            'email' => 'info@limoschedule.test',
            'phone' => '+1 555-0100',
            'whatsapp' => '+1 555-0100',
            'address' => '1 Park Avenue, New York, NY 10016, USA',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'primary_color' => '#c9a24b',
            'secondary_color' => '#9ca3af',
            'accent_color' => '#e6c877',
            'text_color' => '#f5f5f4',
            'background_color' => '#0a0a0a',
            'theme_mode' => 'dark',
        ]);
    }
}
