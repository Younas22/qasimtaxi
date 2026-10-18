<?php

namespace App\Http\Controllers\Admin\Location;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CityController extends LocationCrudController
{
    protected function modelClass(): string
    {
        return City::class;
    }

    protected function viewPath(): string
    {
        return 'admin.locations.cities';
    }

    protected function routeName(): string
    {
        return 'admin.locations.cities';
    }

    protected function entityLabel(): string
    {
        return 'City';
    }

    protected function eagerLoad(): array
    {
        return ['country', 'state'];
    }

    protected function formOptions(): array
    {
        return [
            'countries' => Country::orderBy('name')->get(),
            'states' => State::orderBy('name')->get(['id', 'name', 'country_id']),
        ];
    }

    protected function rules(Request $request, ?Model $model = null): array
    {
        return [
            'state_id' => ['required', 'exists:states,id'],
            'name' => ['required', 'string', 'max:255'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    protected function prepareData(array $data): array
    {
        $data['country_id'] = State::findOrFail($data['state_id'])->country_id;

        return $data;
    }

    protected function dependentsCount(Model $model): int
    {
        return $model->airports()->count()
            + $model->trainStations()->count()
            + $model->pickupPoints()->count();
    }
}
