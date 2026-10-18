<?php

namespace App\Notifications\Admin;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

/**
 * Laravel's stock ResetPassword notification hardcodes the "password.reset"
 * route name, which doesn't exist for the admin guard (registered instead
 * as "admin.password.reset"). This override points at the correct named
 * route so the "forgot password" email link actually resolves.
 */
class ResetPassword extends BaseResetPassword
{
    protected function resetUrl($notifiable): string
    {
        return url(route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
