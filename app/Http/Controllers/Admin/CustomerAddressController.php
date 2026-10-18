<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerAddressController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'address_line' => ['required', 'string', 'max:500'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($customer, $data, $request) {
            if ($request->boolean('is_default')) {
                $customer->addresses()->update(['is_default' => false]);
            }

            $customer->addresses()->create($data + ['is_default' => $request->boolean('is_default')]);
        });

        return back()->with('status', 'Address added successfully.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $address->delete();

        return back()->with('status', 'Address removed successfully.');
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        DB::transaction(function () use ($address) {
            $address->customer->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('status', 'Default address updated.');
    }
}
