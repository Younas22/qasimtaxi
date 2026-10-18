<?php

namespace App\Http\Controllers\Driver\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('driver.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::guard('driver')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        if (! Auth::guard('driver')->user()->status) {
            Auth::guard('driver')->logout();

            return back()->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
        }

        $request->session()->regenerate();

        $driver = Auth::guard('driver')->user();
        $driver->applyPreferencesToSession();

        return redirect()->intended(route('driver.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('driver')->user()?->update(['is_online' => false]);

        Auth::guard('driver')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('driver.login');
    }
}
