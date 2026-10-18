<?php

namespace App\Channels;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Throwable;

/**
 * Mail channel for booking lifecycle notifications. The queue runs
 * synchronously, so a mail provider hiccup (Resend down, bad recipient,
 * unverified domain) would otherwise bubble up and 500 the request that
 * created or updated the booking — after the booking was already saved.
 * A failed email is logged instead, and the remaining channels
 * (in-app, push) still go out.
 */
class SafeMailChannel extends MailChannel
{
    public function send($notifiable, Notification $notification)
    {
        try {
            return parent::send($notifiable, $notification);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}
