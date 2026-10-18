<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::with('category')->latest()->get();

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        $categories = VehicleCategory::orderBy('name')->get();

        return view('admin.vehicles.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateVehicle($request) + $this->featureFlags($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), 'vehicle');
        }

        $vehicle = Vehicle::create($data + ['status' => true]);

        $this->storeGalleryImages($request, $vehicle);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('status', "Vehicle \"{$vehicle->name}\" added successfully.");
    }

    public function edit(Vehicle $vehicle): View
    {
        $categories = VehicleCategory::orderBy('name')->get();
        $vehicle->load(['images', 'reviews.customer']);

        return view('admin.vehicles.edit', compact('vehicle', 'categories'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $this->validateVehicle($request) + $this->featureFlags($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), 'vehicle', $vehicle->image);
        }

        $vehicle->update($data);

        $this->storeGalleryImages($request, $vehicle);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('status', "Vehicle \"{$vehicle->name}\" updated successfully.");
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->deleteUpload($vehicle->image);

        foreach ($vehicle->images as $image) {
            $this->deleteUpload($image->image);
        }

        $vehicle->delete();

        return back()->with('status', 'Vehicle deleted successfully.');
    }

    public function toggleStatus(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update(['status' => ! $vehicle->status]);

        return back()->with('status', $vehicle->status ? "\"{$vehicle->name}\" enabled." : "\"{$vehicle->name}\" disabled.");
    }

    public function destroyImage(VehicleImage $image): RedirectResponse
    {
        $this->deleteUpload($image->image);
        $image->delete();

        return back()->with('status', 'Image removed.');
    }

    private function validateVehicle(Request $request): array
    {
        return $request->validate([
            'vehicle_category_id' => ['required', 'exists:vehicle_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1990', 'max:'.(now()->year + 1)],
            'plate_number' => ['nullable', 'string', 'max:20'],
            'seats' => ['required', 'integer', 'min:1', 'max:60'],
            'luggage' => ['required', 'integer', 'min:0', 'max:60'],
            'transmission' => ['required', 'in:automatic,manual'],
            'fuel_type' => ['required', 'in:petrol,diesel,hybrid,electric'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'images.*' => ['nullable', 'image', 'max:2048'],
            'base_fare' => ['required', 'numeric', 'min:0'],
            'price_per_km' => ['required', 'numeric', 'min:0'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'airport_price' => ['required', 'numeric', 'min:0'],
            'night_charges' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /**
     * Checkboxes are absent from the request entirely when unchecked, so each
     * flag must be read explicitly rather than relying on validated() output.
     */
    private function featureFlags(Request $request): array
    {
        return [
            'has_wifi' => $request->boolean('has_wifi'),
            'has_water' => $request->boolean('has_water'),
            'has_charger' => $request->boolean('has_charger'),
            'has_baby_seat' => $request->boolean('has_baby_seat'),
            'has_ac' => $request->boolean('has_ac'),
        ];
    }

    private function storeGalleryImages(Request $request, Vehicle $vehicle): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $nextSortOrder = $vehicle->images()->max('sort_order') + 1;

        foreach ($request->file('images') as $index => $file) {
            VehicleImage::create([
                'vehicle_id' => $vehicle->id,
                'image' => $this->storeUpload($file, 'gallery'),
                'sort_order' => $nextSortOrder + $index,
            ]);
        }
    }

    private function storeUpload($file, string $prefix, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/vehicles');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $prefix.'-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
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

        $path = public_path('uploads/vehicles'.DIRECTORY_SEPARATOR.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
