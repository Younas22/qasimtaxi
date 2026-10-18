<?php

namespace App\Http\Controllers\Admin\Location;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class StateController extends LocationCrudController
{
    protected function modelClass(): string
    {
        return State::class;
    }

    protected function viewPath(): string
    {
        return 'admin.locations.states';
    }

    protected function routeName(): string
    {
        return 'admin.locations.states';
    }

    protected function entityLabel(): string
    {
        return 'State';
    }

    protected function eagerLoad(): array
    {
        return ['country'];
    }

    protected function formOptions(): array
    {
        return ['countries' => Country::orderBy('name')->get()];
    }

    protected function rules(Request $request, ?Model $model = null): array
    {
        return [
            'country_id' => ['required', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:10'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    protected function dependentsCount(Model $model): int
    {
        return $model->cities()->count();
    }
}
