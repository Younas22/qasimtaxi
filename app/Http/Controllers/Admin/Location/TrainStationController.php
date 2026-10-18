<?php

namespace App\Http\Controllers\Admin\Location;

use App\Models\City;
use App\Models\TrainStation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TrainStationController extends LocationCrudController
{
    protected function modelClass(): string
    {
        return TrainStation::class;
    }

    protected function viewPath(): string
    {
        return 'admin.locations.train-stations';
    }

    protected function routeName(): string
    {
        return 'admin.locations.train-stations';
    }

    protected function entityLabel(): string
    {
        return 'Train station';
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
}
