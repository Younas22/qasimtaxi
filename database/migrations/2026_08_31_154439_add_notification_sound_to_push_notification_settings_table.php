<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-uploaded custom sound file for browser push notifications (see
     * PushNotificationSettingController::update()) — stored filename only,
     * same pattern as Setting::logo/favicon. Null means "use the browser's
     * own default notification sound".
     */
    public function up(): void
    {
        Schema::table('push_notification_settings', function (Blueprint $table) {
            $table->string('notification_sound')->nullable()->after('push_driver_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('push_notification_settings', function (Blueprint $table) {
            $table->dropColumn('notification_sound');
        });
    }
};
