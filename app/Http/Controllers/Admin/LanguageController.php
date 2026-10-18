<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        $languages = Language::withCount('translations')->orderBy('name')->get();

        return view('admin.languages.index', compact('languages'));
    }

    public function create(): View
    {
        return view('admin.languages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLanguage($request);

        if ($request->hasFile('flag')) {
            $data['flag'] = $this->storeFlag($request->file('flag'));
        }

        $language = Language::create($data + ['is_active' => true]);

        if (Language::count() === 1) {
            $language->update(['is_default' => true]);
        }

        return redirect()->route('admin.languages.index')->with('status', "Language \"{$language->name}\" added successfully.");
    }

    public function edit(Language $language): View
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(Request $request, Language $language): RedirectResponse
    {
        $data = $this->validateLanguage($request, $language);

        if ($request->hasFile('flag')) {
            $data['flag'] = $this->storeFlag($request->file('flag'), $language->flag);
        }

        $language->update($data);

        return redirect()->route('admin.languages.index')->with('status', "Language \"{$language->name}\" updated successfully.");
    }

    public function destroy(Language $language): RedirectResponse
    {
        abort_if($language->is_default, 403, 'The default language cannot be deleted.');
        abort_if(Language::count() <= 1, 403, 'At least one language must remain.');

        $language->delete();

        return back()->with('status', 'Language deleted successfully.');
    }

    public function toggleStatus(Language $language): RedirectResponse
    {
        abort_if($language->is_default && $language->is_active, 403, 'The default language cannot be disabled.');

        $language->update(['is_active' => ! $language->is_active]);

        return back()->with('status', $language->is_active ? "\"{$language->name}\" enabled." : "\"{$language->name}\" disabled.");
    }

    public function setDefault(Language $language): RedirectResponse
    {
        abort_unless($language->is_active, 403, 'Only an enabled language can be set as default.');

        DB::transaction(function () use ($language) {
            Language::where('is_default', true)->update(['is_default' => false]);
            $language->update(['is_default' => true]);
        });

        return back()->with('status', "\"{$language->name}\" is now the default language.");
    }

    private function validateLanguage(Request $request, ?Language $language = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'native_name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'regex:/^[a-zA-Z-]+$/', 'unique:languages,code,'.($language?->id)],
            'flag' => ['nullable', 'image', 'max:512'],
            'direction' => ['required', 'in:ltr,rtl'],
        ]);
    }

    private function storeFlag($file, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/languages');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'flag-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        if ($previousFilename && file_exists($directory.DIRECTORY_SEPARATOR.$previousFilename)) {
            @unlink($directory.DIRECTORY_SEPARATOR.$previousFilename);
        }

        return $filename;
    }
}
