<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('notification_settings')->insertOrIgnore([
            'event_type' => 'booking_status_updated',
            'label' => 'Booking Status Updated',
            'email_enabled' => true,
            'admin_panel_enabled' => true,
            'sms_enabled' => false,
            'push_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('notification.settings');
    }

    public function down(): void
    {
        DB::table('notification_settings')->where('event_type', 'booking_status_updated')->delete();

        Cache::forget('notification.settings');
    }
};
