<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerLoyaltyController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['earned', 'redeemed'])],
            'points' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['type'] === 'redeemed' && $data['points'] > $customer->loyalty_points) {
            return back()->withErrors(['points' => 'Redeemed points exceed the current loyalty balance.']);
        }

        $adminId = auth('admin')->id();

        $data['type'] === 'earned'
            ? $customer->earnLoyaltyPoints($data['points'], $data['reason'], $adminId)
            : $customer->redeemLoyaltyPoints($data['points'], $data['reason'], $adminId);

        return back()->with('status', 'Loyalty points updated successfully.');
    }
}
