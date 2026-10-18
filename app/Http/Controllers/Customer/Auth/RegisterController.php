<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Customer;
use App\Services\CustomerSessionTracker;
use App\Services\PushNotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('customer.auth.register');
    }

    public function store(Request $request, CustomerSessionTracker $sessionTracker, PushNotificationService $pushNotifications): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => true,
        ]);

        event(new Registered($customer));

        foreach (Admin::withPermission('customers.view')->get() as $admin) {
            $pushNotifications->send(
                $admin,
                'new_customer',
                __('New Customer'),
                __(':name just created an account.', ['name' => $customer->name]),
                route('admin.customers.show', $customer),
                null,
                ['customer_id' => $customer->id]
            );
        }

        Auth::guard('customer')->login($customer);

        $request->session()->regenerate();

        $sessionTracker->record($customer, $request);

        return redirect()->route('customer.dashboard')->with('status', 'Welcome! Your account has been created.');
    }
}
