<?php

namespace App\Notifications\Customer;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

/**
 * Laravel's stock ResetPassword notification hardcodes the "password.reset"
 * route name, which doesn't exist for the customer guard (registered instead
 * as "customer.password.reset"). This override points at the correct named
 * route so the "forgot password" email link actually resolves.
 */
class ResetPassword extends BaseResetPassword
{
    protected function resetUrl($notifiable): string
    {
        return url(route('customer.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
