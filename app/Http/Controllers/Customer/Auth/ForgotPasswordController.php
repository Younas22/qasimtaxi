<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('customer.auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $broker = Password::broker('customers');

        try {
            $status = $broker->sendResetLink($request->only('email'));
        } catch (TransportExceptionInterface $e) {
            report($e);

            // Drop the stored token so the throttle doesn't block an immediate retry.
            if ($user = $broker->getUser($request->only('email'))) {
                $broker->deleteToken($user);
            }

            return back()->withInput($request->only('email'))->withErrors([
                'email' => __('We could not send the reset email right now. Please try again later or contact support.'),
            ]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
