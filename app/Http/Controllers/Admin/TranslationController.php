<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use App\Services\TranslationGroupResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TranslationController extends Controller
{
    public function edit(Language $language, TranslationGroupResolver $groups): View
    {
        $groupedKeys = $groups->groupedKeys();

        $values = Translation::where('language_id', $language->id)
            ->where('group', '*')
            ->pluck('value', 'key');

        return view('admin.languages.translations', compact('language', 'groupedKeys', 'values'));
    }

    /**
     * Re-scans the source tree for which page/area each translation key
     * belongs to — see TranslationGroupResolver. Needed after new pages or
     * new __() strings are added, since the grouping is cached forever
     * otherwise.
     */
    public function rescan(Language $language, TranslationGroupResolver $groups): RedirectResponse
    {
        $groups->refresh();

        return redirect()
            ->route('admin.languages.translations.edit', $language)
            ->with('status', 'Pages rescanned — tabs are up to date.');
    }

    public function update(Request $request, Language $language): RedirectResponse
    {
        $data = $request->validate([
            'translations' => ['array'],
            'translations.*' => ['nullable', 'string'],
            'new_translations' => ['array'],
            'new_translations.*.key' => ['nullable', 'string', 'max:255'],
            'new_translations.*.value' => ['nullable', 'string'],
        ]);

        foreach ($data['translations'] ?? [] as $key => $value) {
            Translation::updateOrCreate(
                ['language_id' => $language->id, 'group' => '*', 'key' => $key],
                ['value' => $value ?? '']
            );
        }

        foreach ($data['new_translations'] ?? [] as $row) {
            if (filled($row['key'] ?? null)) {
                Translation::updateOrCreate(
                    ['language_id' => $language->id, 'group' => '*', 'key' => $row['key']],
                    ['value' => $row['value'] ?? '']
                );
            }
        }

        return back()->with('status', 'Translations updated successfully.');
    }

    public function destroy(Request $request, Language $language): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
        ]);

        Translation::where('language_id', $language->id)
            ->where('group', '*')
            ->where('key', $data['key'])
            ->delete();

        return back()->with('status', 'String deleted successfully.');
    }
}
