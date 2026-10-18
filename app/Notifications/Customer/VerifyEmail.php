<?php

namespace App\Notifications\Customer;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Laravel's stock VerifyEmail notification hardcodes the "verification.verify"
 * route name, which doesn't exist for the customer guard (registered instead
 * as "customer.verification.verify"). This override points at the correct
 * named route so the customer-facing "Resend verification email" flow works.
 */
class VerifyEmail extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'customer.verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
