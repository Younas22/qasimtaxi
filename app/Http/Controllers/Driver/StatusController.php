<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    public function toggle(PushNotificationService $pushNotifications): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        $driver->update(['is_online' => ! $driver->is_online]);

        foreach (Admin::withPermission('drivers.view')->get() as $admin) {
            $pushNotifications->send(
                $admin,
                'driver_status_update',
                __('Driver Status Update'),
                $driver->is_online
                    ? __(':name is now online.', ['name' => $driver->name])
                    : __(':name is now offline.', ['name' => $driver->name]),
                route('admin.drivers.edit', $driver),
                null,
                ['driver_id' => $driver->id]
            );
        }

        return back()->with('status', $driver->is_online ? __('You are now online.') : __('You are now offline.'));
    }
}
