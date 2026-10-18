<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-row settings table (same singleton pattern as Setting/
     * BookingSetting — see App\Models\PushNotificationSetting::current()),
     * dedicated to browser push rather than folded into the existing
     * NotificationSetting table: that table's push_enabled column is a
     * single shared flag per event across every audience, whereas the
     * brief asks for master → role → per-role-event granularity, plus a
     * "Driver" audience that has no presence in NotificationSetting at all.
     *
     * Default: master OFF, every role and event ON — so turning the master
     * switch on for the first time immediately behaves like "everything is
     * on" rather than admin having to also flip 25 individual toggles.
     */
    public function up(): void
    {
        Schema::create('push_notification_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('push_notifications_enabled')->default(false);

            $table->boolean('push_admin_enabled')->default(true);
            $table->boolean('push_customer_enabled')->default(true);
            $table->boolean('push_driver_enabled')->default(true);

            // Admin event types
            $table->boolean('push_admin_new_booking')->default(true);
            $table->boolean('push_admin_booking_cancelled')->default(true);
            $table->boolean('push_admin_payment_received')->default(true);
            $table->boolean('push_admin_new_customer')->default(true);
            $table->boolean('push_admin_new_driver')->default(true);
            $table->boolean('push_admin_driver_status_update')->default(true);
            $table->boolean('push_admin_booking_status_update')->default(true);
            $table->boolean('push_admin_system_alerts')->default(true);

            // Driver event types
            $table->boolean('push_driver_booking_assigned')->default(true);
            $table->boolean('push_driver_booking_updated')->default(true);
            $table->boolean('push_driver_booking_cancelled')->default(true);
            $table->boolean('push_driver_pickup_reminder')->default(true);
            $table->boolean('push_driver_customer_update')->default(true);
            $table->boolean('push_driver_trip_update')->default(true);
            $table->boolean('push_driver_dispatch_update')->default(true);

            // Customer event types
            $table->boolean('push_customer_booking_created')->default(true);
            $table->boolean('push_customer_booking_confirmed')->default(true);
            $table->boolean('push_customer_driver_assigned')->default(true);
            $table->boolean('push_customer_driver_accepted')->default(true);
            $table->boolean('push_customer_driver_arriving')->default(true);
            $table->boolean('push_customer_trip_started')->default(true);
            $table->boolean('push_customer_trip_completed')->default(true);
            $table->boolean('push_customer_booking_cancelled')->default(true);
            $table->boolean('push_customer_payment_received')->default(true);
            $table->boolean('push_customer_invoice_ready')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_settings');
    }
};
