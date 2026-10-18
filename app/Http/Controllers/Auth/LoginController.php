<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\CustomerSessionTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Single front-door login for every audience of this app (customers,
 * admins, and drivers), which otherwise authenticate against entirely
 * separate guards and tables. Rather than asking the visitor which one
 * they are, one form tries the customer guard first (the far more common
 * visitor), then the admin guard, then the driver guard, and routes to
 * whichever dashboard matches.
 *
 * The guard-specific /account/login, /admin/login, and /driver/login
 * routes still exist (internal redirects, password-reset flows, etc. all
 * reference them by name) but now simply forward here — this is the only
 * login screen a person actually sees.
 */
class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::guard('driver')->check()) {
            return redirect()->route('driver.dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request, CustomerSessionTracker $sessionTracker): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('customer')->attempt($credentials, $remember)) {
            $customer = Auth::guard('customer')->user();

            if (! $customer->status) {
                Auth::guard('customer')->logout();

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
            }

            $request->session()->regenerate();
            $sessionTracker->record($customer, $request);
            $customer->applyPreferencesToSession();

            return redirect()->intended(route('customer.dashboard'));
        }

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $admin = Auth::guard('admin')->user();

            if (! $admin->status) {
                Auth::guard('admin')->logout();

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Your admin account has been deactivated.']);
            }

            $request->session()->regenerate();
            ActivityLog::record('login', $admin->name.' logged in.', $request);

            return redirect()->intended(route('admin.dashboard'));
        }

        if (Auth::guard('driver')->attempt($credentials, $remember)) {
            $driver = Auth::guard('driver')->user();

            if (! $driver->status) {
                Auth::guard('driver')->logout();

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
            }

            $request->session()->regenerate();
            $driver->applyPreferencesToSession();

            return redirect()->intended(route('driver.dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }
}
