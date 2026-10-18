<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomerActiveSession;
use App\Services\CustomerSessionTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('customer.auth.login');
    }

    public function store(Request $request, CustomerSessionTracker $sessionTracker): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::guard('customer')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        if (! Auth::guard('customer')->user()->status) {
            Auth::guard('customer')->logout();

            return back()->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
        }

        $request->session()->regenerate();

        $customer = Auth::guard('customer')->user();
        $sessionTracker->record($customer, $request);
        $customer->applyPreferencesToSession();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        CustomerActiveSession::where('session_id', $request->session()->getId())->delete();

        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
