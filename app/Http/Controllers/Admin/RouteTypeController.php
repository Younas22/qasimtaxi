<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RouteType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RouteTypeController extends Controller
{
    public function index(): View
    {
        $routeTypes = RouteType::withCount('popularRoutes')->ordered()->get();

        return view('admin.route-types.index', compact('routeTypes'));
    }

    public function create(): View
    {
        return view('admin.route-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRouteType($request);

        $routeType = RouteType::create($data + ['is_active' => true]);

        return redirect()
            ->route('admin.popular-routes.route-types.index')
            ->with('status', "Route type \"{$routeType->name}\" added successfully.");
    }

    public function edit(RouteType $routeType): View
    {
        return view('admin.route-types.edit', compact('routeType'));
    }

    public function update(Request $request, RouteType $routeType): RedirectResponse
    {
        $data = $this->validateRouteType($request, $routeType);
        $data['slug'] = $routeType->uniqueSlug($data['name']);

        $routeType->update($data);

        return redirect()
            ->route('admin.popular-routes.route-types.index')
            ->with('status', "Route type \"{$routeType->name}\" updated successfully.");
    }

    public function destroy(RouteType $routeType): RedirectResponse
    {
        if ($routeType->popularRoutes()->exists()) {
            return back()->with('error', "Cannot delete \"{$routeType->name}\" — it still has routes assigned to it.");
        }

        $routeType->delete();

        return back()->with('status', 'Route type deleted successfully.');
    }

    public function toggleStatus(RouteType $routeType): RedirectResponse
    {
        $routeType->update(['is_active' => ! $routeType->is_active]);

        return back()->with('status', $routeType->is_active ? "\"{$routeType->name}\" enabled." : "\"{$routeType->name}\" disabled.");
    }

    public function moveUp(RouteType $routeType): RedirectResponse
    {
        $previous = RouteType::where('sort_order', '<', $routeType->sort_order)->orderByDesc('sort_order')->first();

        if ($previous) {
            DB::transaction(function () use ($routeType, $previous) {
                [$routeType->sort_order, $previous->sort_order] = [$previous->sort_order, $routeType->sort_order];
                $routeType->save();
                $previous->save();
            });
        }

        return back();
    }

    public function moveDown(RouteType $routeType): RedirectResponse
    {
        $next = RouteType::where('sort_order', '>', $routeType->sort_order)->orderBy('sort_order')->first();

        if ($next) {
            DB::transaction(function () use ($routeType, $next) {
                [$routeType->sort_order, $next->sort_order] = [$next->sort_order, $routeType->sort_order];
                $routeType->save();
                $next->save();
            });
        }

        return back();
    }

    private function validateRouteType(Request $request, ?RouteType $routeType = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
