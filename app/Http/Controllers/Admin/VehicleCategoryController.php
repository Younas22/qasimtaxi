<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VehicleCategoryController extends Controller
{
    public function index(): View
    {
        $categories = VehicleCategory::ordered()->get();

        return view('admin.vehicles.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.vehicles.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->storeUpload($request->file('icon'), 'icon');
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), 'image');
        }

        $category = VehicleCategory::create($data + ['is_active' => true]);

        return redirect()
            ->route('admin.vehicles.categories.index')
            ->with('status', "Category \"{$category->name}\" added successfully.");
    }

    public function edit(VehicleCategory $category): View
    {
        return view('admin.vehicles.categories.edit', compact('category'));
    }

    public function update(Request $request, VehicleCategory $category): RedirectResponse
    {
        $data = $this->validateCategory($request, $category);

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->storeUpload($request->file('icon'), 'icon', $category->icon);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), 'image', $category->image);
        }

        $data['slug'] = $category->uniqueSlug($data['name']);

        $category->update($data);

        return redirect()
            ->route('admin.vehicles.categories.index')
            ->with('status', "Category \"{$category->name}\" updated successfully.");
    }

    public function destroy(VehicleCategory $category): RedirectResponse
    {
        $this->deleteUpload($category->icon);
        $this->deleteUpload($category->image);

        $category->delete();

        return back()->with('status', 'Category deleted successfully.');
    }

    public function toggleStatus(VehicleCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('status', $category->is_active ? "\"{$category->name}\" enabled." : "\"{$category->name}\" disabled.");
    }

    public function moveUp(VehicleCategory $category): RedirectResponse
    {
        $previous = VehicleCategory::where('sort_order', '<', $category->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($previous) {
            DB::transaction(function () use ($category, $previous) {
                [$category->sort_order, $previous->sort_order] = [$previous->sort_order, $category->sort_order];
                $category->save();
                $previous->save();
            });
        }

        return back();
    }

    public function moveDown(VehicleCategory $category): RedirectResponse
    {
        $next = VehicleCategory::where('sort_order', '>', $category->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            DB::transaction(function () use ($category, $next) {
                [$category->sort_order, $next->sort_order] = [$next->sort_order, $category->sort_order];
                $category->save();
                $next->save();
            });
        }

        return back();
    }

    private function validateCategory(Request $request, ?VehicleCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'image', 'max:512'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function storeUpload($file, string $prefix, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/vehicle-categories');

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

        $path = public_path('uploads/vehicle-categories'.DIRECTORY_SEPARATOR.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
