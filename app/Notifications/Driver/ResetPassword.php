<?php

namespace App\Notifications\Driver;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

/**
 * Laravel's stock ResetPassword notification hardcodes the "password.reset"
 * route name, which doesn't exist for the driver guard (registered instead
 * as "driver.password.reset"). This override points at the correct named
 * route so the "forgot password" email link actually resolves.
 */
class ResetPassword extends BaseResetPassword
{
    protected function resetUrl($notifiable): string
    {
        return url(route('driver.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
