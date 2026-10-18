<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(): View
    {
        $redirects = Redirect::latest()->paginate(20);

        return view('admin.redirects.index', compact('redirects'));
    }

    public function create(): View
    {
        return view('admin.redirects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRedirect($request);

        Redirect::create($data + ['is_active' => true]);

        return redirect()
            ->route('admin.redirects.index')
            ->with('status', 'Redirect added successfully.');
    }

    public function edit(Redirect $redirect): View
    {
        return view('admin.redirects.edit', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect): RedirectResponse
    {
        $data = $this->validateRedirect($request, $redirect);
        $data['is_active'] = $request->boolean('is_active');

        $redirect->update($data);

        return redirect()
            ->route('admin.redirects.index')
            ->with('status', 'Redirect updated successfully.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();

        return back()->with('status', 'Redirect deleted successfully.');
    }

    private function validateRedirect(Request $request, ?Redirect $redirect = null): array
    {
        // Normalize before validating so the uniqueness check compares
        // against the same slash-free form every stored row is saved in
        // (Redirect::booted() normalizes again on save as a second safety
        // net, but doing it here too means the uniqueness check itself is
        // accurate rather than comparing a raw "/old-page/" against a
        // stored "old-page").
        $request->merge([
            'old_path' => Redirect::normalizePath((string) $request->input('old_path')),
            'new_path' => Redirect::normalizePath((string) $request->input('new_path')),
        ]);

        return $request->validate([
            'old_path' => [
                'required', 'string', 'max:255',
                Rule::unique('redirects', 'old_path')->ignore($redirect?->id),
            ],
            'new_path' => ['required', 'string', 'max:255', 'different:old_path'],
            'type' => ['required', Rule::in(array_keys(Redirect::TYPES))],
        ]);
    }
}
