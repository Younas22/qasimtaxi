<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::guard('admin')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        if (! Auth::guard('admin')->user()->status) {
            Auth::guard('admin')->logout();

            return back()->withErrors(['email' => 'Your admin account has been deactivated.']);
        }

        $request->session()->regenerate();

        ActivityLog::record('login', Auth::guard('admin')->user()->name.' logged in.', $request);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($admin) {
            ActivityLog::record('logout', $admin->name.' logged out.', $request, $admin);
        }

        return redirect()->route('admin.login');
    }
}
