<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\EmailSetting;
use App\Notifications\BookingNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Single entry point for admin-facing booking notifications: in-app/push to
 * every admin who can view bookings, plus an email to the admin inbox set
 * under Email Settings (falling back to each admin's own login email when
 * no inbox is configured — see BookingNotification::via()).
 */
class AdminBookingNotifier
{
    public function send(BookingNotification $notification): void
    {
        $admins = Admin::withPermission('bookings.view')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notification);
        }

        $inbox = EmailSetting::current()->adminNotificationEmails();

        if ($inbox !== []) {
            Notification::route('mail', $inbox)->notify($notification);
        }
    }
}
