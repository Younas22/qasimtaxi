<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $customer = Auth::guard('customer')->user();

        // First/last name were added after `name`, so existing accounts
        // won't have them set yet — fall back to a best-effort split so the
        // form doesn't render blank fields for pre-existing customers.
        if (! $customer->first_name && ! $customer->last_name && $customer->name) {
            $parts = preg_split('/\s+/', trim($customer->name), 2);
            $customer->first_name = $parts[0] ?? null;
            $customer->last_name = $parts[1] ?? null;
        }

        return view('customer.profile.edit', compact('customer'));
    }

    public function update(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(array_keys(\App\Models\Customer::GENDERS))],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->storeUpload($request->file('avatar'), $customer->avatar);
        }

        $customer->update($data);

        return back()->with('status', 'Profile updated successfully.');
    }

    private function storeUpload($file, ?string $previousFilename): string
    {
        $directory = public_path('uploads/customers');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'avatar-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        if ($previousFilename && file_exists($directory.DIRECTORY_SEPARATOR.$previousFilename)) {
            @unlink($directory.DIRECTORY_SEPARATOR.$previousFilename);
        }

        return $filename;
    }
}
