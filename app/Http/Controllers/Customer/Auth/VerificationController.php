<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Email verification scaffolding — wired up and ready to enforce once the
 * project's outbound mail transport is fully configured, but not yet
 * gating dashboard access (see the `customer.auth` route group).
 */
class VerificationController extends Controller
{
    public function notice(): View|RedirectResponse
    {
        return Auth::guard('customer')->user()->hasVerifiedEmail()
            ? redirect()->route('customer.dashboard')
            : view('customer.auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user('customer')->hasVerifiedEmail()) {
            return redirect()->route('customer.dashboard');
        }

        $request->fulfill();

        return redirect()->route('customer.dashboard')->with('status', 'Your email has been verified.');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user('customer')->hasVerifiedEmail()) {
            return redirect()->route('customer.dashboard');
        }

        $request->user('customer')->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }
}
