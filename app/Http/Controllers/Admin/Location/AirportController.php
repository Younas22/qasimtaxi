<?php

namespace App\Http\Controllers\Admin\Location;

use App\Models\Airport;
use App\Models\City;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AirportController extends LocationCrudController
{
    protected function modelClass(): string
    {
        return Airport::class;
    }

    protected function viewPath(): string
    {
        return 'admin.locations.airports';
    }

    protected function routeName(): string
    {
        return 'admin.locations.airports';
    }

    protected function entityLabel(): string
    {
        return 'Airport';
    }

    protected function eagerLoad(): array
    {
        return ['city.state.country'];
    }

    protected function formOptions(): array
    {
        return ['cities' => City::with(['state', 'country'])->orderBy('name')->get()];
    }

    protected function rules(Request $request, ?Model $model = null): array
    {
        return [
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:10'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    protected function prepareData(array $data): array
    {
        if (! empty($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        return $data;
    }
}
