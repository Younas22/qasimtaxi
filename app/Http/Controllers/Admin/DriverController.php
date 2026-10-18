<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(): View
    {
        $drivers = Driver::with('vehicle')->latest()->get();

        return view('admin.drivers.index', compact('drivers'));
    }

    public function create(): View
    {
        $vehicles = Vehicle::orderBy('name')->get();

        return view('admin.drivers.create', compact('vehicles'));
    }

    public function store(Request $request, PushNotificationService $pushNotifications): RedirectResponse
    {
        $data = $this->validateDriver($request);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storeUpload($request->file('photo'));
        }

        $data['password'] = filled($data['password'] ?? null) ? Hash::make($data['password']) : null;

        $driver = Driver::create($data + ['status' => true]);

        foreach (Admin::withPermission('drivers.view')->get() as $admin) {
            $pushNotifications->send(
                $admin,
                'new_driver',
                __('New Driver'),
                __(':name has been added as a driver.', ['name' => $driver->name]),
                route('admin.drivers.edit', $driver),
                null,
                ['driver_id' => $driver->id]
            );
        }

        return redirect()
            ->route('admin.drivers.index')
            ->with('status', "Driver \"{$driver->name}\" added successfully.");
    }

    public function edit(Driver $driver): View
    {
        $vehicles = Vehicle::orderBy('name')->get();
        $driver->load('reviews.customer');

        return view('admin.drivers.edit', compact('driver', 'vehicles'));
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $data = $this->validateDriver($request, $driver);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storeUpload($request->file('photo'), $driver->photo);
        }

        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $driver->update($data);

        return redirect()
            ->route('admin.drivers.index')
            ->with('status', "Driver \"{$driver->name}\" updated successfully.");
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        $this->deleteUpload($driver->photo);
        $driver->delete();

        return back()->with('status', 'Driver deleted successfully.');
    }

    public function toggleStatus(Driver $driver): RedirectResponse
    {
        $driver->update(['status' => ! $driver->status]);

        return back()->with('status', $driver->status ? "\"{$driver->name}\" enabled." : "\"{$driver->name}\" disabled.");
    }

    public function toggleOnline(Driver $driver): RedirectResponse
    {
        $driver->update(['is_online' => ! $driver->is_online]);

        return back()->with('status', $driver->is_online ? "\"{$driver->name}\" is now online." : "\"{$driver->name}\" is now offline.");
    }

    public function toggleAvailable(Driver $driver): RedirectResponse
    {
        $driver->update(['is_available' => ! $driver->is_available]);

        return back()->with('status', $driver->is_available ? "\"{$driver->name}\" is now available." : "\"{$driver->name}\" is now unavailable.");
    }

    private function validateDriver(Request $request, ?Driver $driver = null): array
    {
        return $request->validate([
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('drivers', 'email')->ignore($driver?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function storeUpload($file, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/drivers');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'driver-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
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

        $path = public_path('uploads/drivers'.DIRECTORY_SEPARATOR.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
