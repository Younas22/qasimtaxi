<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::withCount('bookings')->latest()->get();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCustomer($request);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->storeUpload($request->file('avatar'));
        }

        $customer = Customer::create($data + ['status' => true]);

        return redirect()
            ->route('admin.customers.index')
            ->with('status', "Customer \"{$customer->name}\" added successfully.");
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'addresses.city',
            'walletTransactions' => fn ($q) => $q->limit(10),
            'loyaltyTransactions' => fn ($q) => $q->limit(10),
            'reviews.driver',
            'reviews.vehicle',
        ]);

        $bookings = $customer->bookings()->with(['driver', 'vehicle'])->latest()->limit(10)->get();
        $cities = City::orderBy('name')->get();

        return view('admin.customers.show', compact('customer', 'bookings', 'cities'));
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validateCustomer($request, $customer);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->storeUpload($request->file('avatar'), $customer->avatar);
        }

        $customer->update($data);

        return redirect()
            ->route('admin.customers.index')
            ->with('status', "Customer \"{$customer->name}\" updated successfully.");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->deleteUpload($customer->avatar);
        $customer->delete();

        return back()->with('status', 'Customer deleted successfully.');
    }

    public function toggleStatus(Customer $customer): RedirectResponse
    {
        $customer->update(['status' => ! $customer->status]);

        return back()->with('status', $customer->status ? "\"{$customer->name}\" enabled." : "\"{$customer->name}\" disabled.");
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function storeUpload($file, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/customers');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'customer-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        if ($previousFilename && file_exists($directory.DIRECTORY_SEPARATOR.$previousFilename)) {
            @unlink($directory.DIRECTORY_SEPARATOR.$previousFilename);
        }

        return $filename;
    }

    private function deleteUpload(?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('uploads/customers'.DIRECTORY_SEPARATOR.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
