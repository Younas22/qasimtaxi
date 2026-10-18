<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use App\Models\VehicleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingRuleController extends Controller
{
    public function index(): View
    {
        $global = PricingRule::global();
        $categories = VehicleCategory::ordered()->with('pricingRule')->get();

        return view('admin.pricing.index', compact('global', 'categories'));
    }

    public function editGlobal(): View
    {
        $rule = PricingRule::global();

        return view('admin.pricing.edit', ['rule' => $rule, 'category' => null]);
    }

    public function updateGlobal(Request $request): RedirectResponse
    {
        $rule = PricingRule::global();
        $rule->update($this->validateRule($request));

        return redirect()->route('admin.pricing.index')->with('status', 'Global default pricing updated successfully.');
    }

    public function editCategory(VehicleCategory $category): View
    {
        $rule = $category->pricingRule ?? PricingRule::global()
            ->replicate(['id', 'vehicle_category_id', 'label', 'created_at', 'updated_at'])
            ->fill(['vehicle_category_id' => $category->id, 'label' => $category->name, 'is_active' => true]);

        return view('admin.pricing.edit', ['rule' => $rule, 'category' => $category]);
    }

    public function updateCategory(Request $request, VehicleCategory $category): RedirectResponse
    {
        $data = $this->validateRule($request);
        $data['vehicle_category_id'] = $category->id;
        $data['label'] = $category->name;
        $data['is_active'] = $request->boolean('is_active');

        PricingRule::updateOrCreate(['vehicle_category_id' => $category->id], $data);

        return redirect()->route('admin.pricing.index')->with('status', "Pricing for \"{$category->name}\" updated successfully.");
    }

    public function resetCategory(VehicleCategory $category): RedirectResponse
    {
        $category->pricingRule?->delete();

        return back()->with('status', "\"{$category->name}\" now uses the global default pricing.");
    }

    private function validateRule(Request $request): array
    {
        $data = $request->validate([
            'base_fare' => ['required', 'numeric', 'min:0'],
            'base_fare_threshold_km' => ['nullable', 'numeric', 'min:0'],
            'km_fare' => ['required', 'numeric', 'min:0'],
            'mid_distance_threshold_km' => ['nullable', 'numeric', 'min:0'],
            'mid_distance_km_fare' => ['nullable', 'numeric', 'min:0'],
            'long_distance_threshold_km' => ['nullable', 'numeric', 'min:0'],
            'long_distance_km_fare' => ['nullable', 'numeric', 'min:0'],
            'very_long_distance_threshold_km' => ['nullable', 'numeric', 'min:0'],
            'very_long_distance_km_fare' => ['nullable', 'numeric', 'min:0'],
            'hour_fare' => ['required', 'numeric', 'min:0'],
            'waiting_charge_per_minute' => ['required', 'numeric', 'min:0'],
            'free_waiting_minutes' => ['required', 'integer', 'min:0'],
            'night_charge' => ['required', 'numeric', 'min:0'],
            'night_start_time' => ['required', 'date_format:H:i'],
            'night_end_time' => ['required', 'date_format:H:i'],
            'weekend_charge' => ['required', 'numeric', 'min:0'],
            'weekend_days' => ['nullable', 'array'],
            'weekend_days.*' => ['string', 'in:'.implode(',', PricingRule::WEEKDAYS)],
            'toll_charge' => ['required', 'numeric', 'min:0'],
            'airport_surcharge' => ['required', 'numeric', 'min:0'],
            'service_fee' => ['required', 'numeric', 'min:0'],
            'minimum_fare' => ['required', 'numeric', 'min:0'],
            'included_km' => ['required', 'numeric', 'min:0'],
            'included_hours' => ['required', 'numeric', 'min:0'],
            'included_passengers' => ['required', 'integer', 'min:0'],
            'extra_passenger_charge' => ['required', 'numeric', 'min:0'],
            'approach_free_km' => ['required', 'numeric', 'min:0'],
            'approach_km_fare' => ['required', 'numeric', 'min:0'],
            'approach_driver_max_km' => ['required', 'numeric', 'min:0'],
        ]);

        $data['weekend_days'] = $data['weekend_days'] ?? [];

        return $data;
    }
}
