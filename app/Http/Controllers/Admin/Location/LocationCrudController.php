<?php

namespace App\Http\Controllers\Admin\Location;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Shared CRUD behaviour for every location entity (countries, states, cities,
 * airports, train stations, pickup points). Concrete controllers only need to
 * declare the model class, view path, route name, validation rules, and any
 * parent-relation options the form needs — the rest is identical across all six.
 */
abstract class LocationCrudController extends Controller
{
    abstract protected function modelClass(): string;

    /** Blade view directory, e.g. "admin.locations.countries" */
    abstract protected function viewPath(): string;

    /** Route name prefix, e.g. "admin.locations.countries" */
    abstract protected function routeName(): string;

    /** Human label used in flash messages, e.g. "Country" */
    abstract protected function entityLabel(): string;

    /** @return array<string, array<int, string>> */
    abstract protected function rules(Request $request, ?Model $model = null): array;

    /** Data needed by the create/edit form (e.g. ['countries' => Country::all()]) */
    protected function formOptions(): array
    {
        return [];
    }

    /** Relations to eager-load for the index listing */
    protected function eagerLoad(): array
    {
        return [];
    }

    /** How many child records block deletion of the given model, if any */
    protected function dependentsCount(Model $model): int
    {
        return 0;
    }

    /** Hook to normalize validated data before create/update (e.g. uppercase codes) */
    protected function prepareData(array $data): array
    {
        return $data;
    }

    protected function query()
    {
        return $this->modelClass()::query()->with($this->eagerLoad());
    }

    protected function findOrFail(int|string $id): Model
    {
        return $this->modelClass()::with($this->eagerLoad())->findOrFail($id);
    }

    public function index(): View
    {
        $items = $this->query()->orderBy('name')->get();

        return view($this->viewPath().'.index', ['items' => $items] + $this->formOptions());
    }

    public function create(): View
    {
        return view($this->viewPath().'.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->prepareData($request->validate($this->rules($request)));

        $model = $this->modelClass()::create($data + ['is_active' => true]);

        return redirect()
            ->route($this->routeName().'.index')
            ->with('status', "{$this->entityLabel()} \"{$model->name}\" added successfully.");
    }

    public function edit(int|string $id): View
    {
        $model = $this->findOrFail($id);

        return view($this->viewPath().'.edit', ['model' => $model] + $this->formOptions());
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $model = $this->findOrFail($id);

        $model->update($this->prepareData($request->validate($this->rules($request, $model))));

        return redirect()
            ->route($this->routeName().'.index')
            ->with('status', "{$this->entityLabel()} \"{$model->name}\" updated successfully.");
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $model = $this->findOrFail($id);

        $dependents = $this->dependentsCount($model);

        abort_if($dependents > 0, 403, "Cannot delete \"{$model->name}\" — {$dependents} linked record(s) depend on it.");

        $model->delete();

        return back()->with('status', "{$this->entityLabel()} deleted successfully.");
    }

    public function toggleStatus(int|string $id): RedirectResponse
    {
        $model = $this->findOrFail($id);

        $model->update(['is_active' => ! $model->is_active]);

        return back()->with('status', $model->is_active ? "\"{$model->name}\" enabled." : "\"{$model->name}\" disabled.");
    }
}
