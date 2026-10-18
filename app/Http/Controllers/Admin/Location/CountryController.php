<?php

namespace App\Http\Controllers\Admin\Location;

use App\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CountryController extends LocationCrudController
{
    protected function modelClass(): string
    {
        return Country::class;
    }

    protected function viewPath(): string
    {
        return 'admin.locations.countries';
    }

    protected function routeName(): string
    {
        return 'admin.locations.countries';
    }

    protected function entityLabel(): string
    {
        return 'Country';
    }

    protected function rules(Request $request, ?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'size:2', 'alpha', 'unique:countries,code,'.$model?->id],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    protected function dependentsCount(Model $model): int
    {
        return $model->states()->count();
    }

    protected function prepareData(array $data): array
    {
        $data['code'] = strtoupper($data['code']);

        return $data;
    }
}
