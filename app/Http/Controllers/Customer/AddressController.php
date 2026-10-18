<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\DialCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        $addresses = Auth::guard('customer')->user()->addresses()->get();

        $countries = DialCode::query()
            ->whereNotNull('iso2')
            ->orderBy('name')
            ->get(['name', 'iso2'])
            ->map(fn ($country) => ['name' => $country->name, 'iso2' => strtolower($country->iso2)])
            ->values();

        return view('customer.addresses.index', compact('addresses', 'countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAddress($request);
        $data['label'] = $this->generateLabel($data);

        $customer = Auth::guard('customer')->user();

        if ($request->boolean('is_default')) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $customer->addresses()->create($data + ['is_default' => $request->boolean('is_default')]);

        return back()->with('status', 'Address added successfully.');
    }

    public function update(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $data = $this->validateAddress($request);
        $data['label'] = $this->generateLabel($data);

        if ($request->boolean('is_default')) {
            $address->customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data + ['is_default' => $request->boolean('is_default')]);

        return back()->with('status', 'Address updated successfully.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $address->delete();

        return back()->with('status', 'Address removed.');
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $address->customer->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('status', 'Default address updated.');
    }

    private function authorizeAddress(CustomerAddress $address): void
    {
        abort_unless($address->customer_id === Auth::guard('customer')->id(), 404);
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'address_line' => ['required', 'string', 'max:500'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'place_id' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'city_name' => ['nullable', 'string', 'max:100'],
        ]);
    }

    /**
     * The address form no longer asks for a manual label — a sensible one
     * is derived instead (the auto-detected city, falling back to the start
     * of the address line) so `customer_addresses.label` still always has a
     * value.
     */
    private function generateLabel(array $data): string
    {
        if (! empty($data['city_name'])) {
            return $data['city_name'];
        }

        if (! empty($data['address_line'])) {
            return Str::limit($data['address_line'], 40);
        }

        return 'Address';
    }
}
