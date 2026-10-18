<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopularRoute;
use App\Models\RouteType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PopularRouteController extends Controller
{
    public function index(Request $request): View
    {
        $routes = PopularRoute::query()
            ->with('routeType')
            ->when($request->filled('route_type_id'), fn ($q) => $q->where('route_type_id', $request->query('route_type_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $routeTypes = RouteType::ordered()->get();

        return view('admin.popular-routes.index', compact('routes', 'routeTypes'));
    }

    public function create(): View
    {
        $routeTypes = RouteType::active()->ordered()->get();

        return view('admin.popular-routes.create', compact('routeTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRoute($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'));
        }

        if ($request->hasFile('hover_image')) {
            $data['hover_image'] = $this->storeUpload($request->file('hover_image'));
        }

        PopularRoute::create($data + ['is_active' => true]);

        return redirect()
            ->route('admin.popular-routes.index')
            ->with('status', 'Route added successfully.');
    }

    public function edit(PopularRoute $popularRoute): View
    {
        $routeTypes = RouteType::active()->ordered()->get();

        return view('admin.popular-routes.edit', ['route' => $popularRoute, 'routeTypes' => $routeTypes]);
    }

    public function update(Request $request, PopularRoute $popularRoute): RedirectResponse
    {
        $data = $this->validateRoute($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), $popularRoute->image);
        } elseif ($request->boolean('remove_image')) {
            $this->deleteUpload($popularRoute->image);
            $data['image'] = null;
        }

        if ($request->hasFile('hover_image')) {
            $data['hover_image'] = $this->storeUpload($request->file('hover_image'), $popularRoute->hover_image);
        } elseif ($request->boolean('remove_hover_image')) {
            $this->deleteUpload($popularRoute->hover_image);
            $data['hover_image'] = null;
        }

        $popularRoute->update($data);

        return redirect()
            ->route('admin.popular-routes.index')
            ->with('status', 'Route updated successfully.');
    }

    public function destroy(PopularRoute $popularRoute): RedirectResponse
    {
        $this->deleteUpload($popularRoute->image);
        $this->deleteUpload($popularRoute->hover_image);

        $popularRoute->delete();

        return back()->with('status', 'Route deleted successfully.');
    }

    public function toggleStatus(PopularRoute $popularRoute): RedirectResponse
    {
        $popularRoute->update(['is_active' => ! $popularRoute->is_active]);

        return back()->with('status', $popularRoute->is_active ? 'Route enabled.' : 'Route disabled.');
    }

    private function validateRoute(Request $request): array
    {
        return $request->validate([
            'route_type_id' => ['required', 'exists:route_types,id'],
            'pickup' => ['required', 'string', 'max:255'],
            'dropoff' => ['required', 'string', 'max:255'],
            'distance' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'distance_unit' => ['required', 'in:km,mi'],
            'estimated_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'original_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'image' => ['nullable', 'image', 'max:2048'],
            'hover_image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function storeUpload($file, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/popular-routes');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'route-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
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

        $path = public_path('uploads/popular-routes'.DIRECTORY_SEPARATOR.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
