<?php

namespace Database\Seeders;

use App\Models\NotificationSetting;
use Illuminate\Database\Seeder;

class NotificationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (NotificationSetting::EVENTS as $eventType => $label) {
            NotificationSetting::firstOrCreate(
                ['event_type' => $eventType],
                [
                    'label' => $label,
                    'email_enabled' => true,
                    'admin_panel_enabled' => true,
                    'sms_enabled' => false,
                    'push_enabled' => false,
                ]
            );
        }
    }
}
