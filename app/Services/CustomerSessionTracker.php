<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerActiveSession;
use App\Models\CustomerLoginHistory;
use Illuminate\Http\Request;

/**
 * Records a permanent login-history entry and an active-session tracking
 * row whenever a customer successfully authenticates (login or register),
 * powering the Security Settings "Active Sessions" / "Login History" views.
 */
class CustomerSessionTracker
{
    public function record(Customer $customer, Request $request): void
    {
        $deviceLabel = device_label($request->userAgent());

        CustomerLoginHistory::create([
            'customer_id' => $customer->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_label' => $deviceLabel,
            'created_at' => now(),
        ]);

        CustomerActiveSession::updateOrCreate(
            ['session_id' => $request->session()->getId()],
            [
                'customer_id' => $customer->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_label' => $deviceLabel,
                'created_at' => now(),
            ]
        );
    }
}
