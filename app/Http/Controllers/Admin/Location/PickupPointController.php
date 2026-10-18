<?php

namespace App\Http\Controllers\Admin\Location;

use App\Models\City;
use App\Models\PickupPoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PickupPointController extends LocationCrudController
{
    protected function modelClass(): string
    {
        return PickupPoint::class;
    }

    protected function viewPath(): string
    {
        return 'admin.locations.pickup-points';
    }

    protected function routeName(): string
    {
        return 'admin.locations.pickup-points';
    }

    protected function entityLabel(): string
    {
        return 'Pickup point';
    }

    protected function eagerLoad(): array
    {
        return ['city.state.country'];
    }

    protected function formOptions(): array
    {
        return [
            'cities' => City::with(['state', 'country'])->orderBy('name')->get(),
            'types' => [
                'hotel' => 'Hotel',
                'landmark' => 'Landmark',
                'business' => 'Business',
                'residential' => 'Residential',
                'other' => 'Other',
            ],
        ];
    }

    protected function rules(Request $request, ?Model $model = null): array
    {
        return [
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['hotel', 'landmark', 'business', 'residential', 'other'])],
            'address' => ['nullable', 'string', 'max:255'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
