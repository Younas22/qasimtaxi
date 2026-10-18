<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::withCount('sections')->orderByRaw(
            "FIELD(slug, '".implode("','", array_keys(Page::PAGES))."')"
        )->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page): View
    {
        $page->load('sections');

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'max:4096'],
            'canonical_override' => ['nullable', 'url', 'max:255'],
            'custom_schema' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['robots_index'] = $request->boolean('robots_index');
        $data['robots_follow'] = $request->boolean('robots_follow');

        if ($request->hasFile('og_image')) {
            $data['og_image'] = $this->storeUpload($request->file('og_image'), 'og-image', $page->og_image);
        }

        $page->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', "\"{$page->name}\" page updated successfully.");
    }

    private function storeUpload($file, string $prefix, ?string $previousFilename): string
    {
        $directory = public_path('uploads/pages');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $prefix.'-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        if ($previousFilename && file_exists($directory.DIRECTORY_SEPARATOR.$previousFilename)) {
            @unlink($directory.DIRECTORY_SEPARATOR.$previousFilename);
        }

        return $filename;
    }
}
