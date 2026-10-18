<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerActiveSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function edit(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        $currentSessionId = $request->session()->getId();

        $activeSessions = $customer->activeSessions()
            ->get()
            ->filter(fn (CustomerActiveSession $session) => $session->session_id === $currentSessionId || $session->isLive())
            ->values();

        $loginHistories = $customer->loginHistories()->paginate(10);

        return view('customer.security.edit', [
            'activeSessions' => $activeSessions,
            'currentSessionId' => $currentSessionId,
            'loginHistories' => $loginHistories,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $customer->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $customer->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Password updated successfully.');
    }

    public function revokeSession(Request $request, CustomerActiveSession $session): RedirectResponse
    {
        abort_unless($session->customer_id === Auth::guard('customer')->id(), 404);

        if ($session->session_id === $request->session()->getId()) {
            return back()->with('error', "You can't revoke your current session. Use logout instead.");
        }

        DB::table('sessions')->where('id', $session->session_id)->delete();
        $session->delete();

        return back()->with('status', 'Session revoked successfully.');
    }

    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $currentSessionId = $request->session()->getId();

        $otherSessionIds = $customer->activeSessions()
            ->where('session_id', '!=', $currentSessionId)
            ->pluck('session_id');

        DB::table('sessions')->whereIn('id', $otherSessionIds)->delete();
        $customer->activeSessions()->where('session_id', '!=', $currentSessionId)->delete();

        return back()->with('status', 'All other sessions have been logged out.');
    }
}
