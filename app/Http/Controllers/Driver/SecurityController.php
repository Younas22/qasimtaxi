<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function edit(): View
    {
        return view('driver.security.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $driver->password || ! Hash::check($data['current_password'], $driver->password)) {
            return back()->withErrors(['current_password' => __('Your current password is incorrect.')]);
        }

        $driver->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', __('Password updated successfully.'));
    }
}
