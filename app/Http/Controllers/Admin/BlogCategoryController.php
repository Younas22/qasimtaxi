<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        $categories = BlogCategory::withCount('posts')->ordered()->get();

        return view('admin.blog.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.blog.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'));
        }

        $category = BlogCategory::create($data + ['is_active' => true]);

        return redirect()
            ->route('admin.blog.categories.index')
            ->with('status', "Category \"{$category->name}\" added successfully.");
    }

    public function edit(BlogCategory $category): View
    {
        return view('admin.blog.categories.edit', compact('category'));
    }

    public function update(Request $request, BlogCategory $category): RedirectResponse
    {
        $data = $this->validateCategory($request, $category);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), $category->image);
        }

        $data['slug'] = $category->uniqueSlug($data['name']);

        $category->update($data);

        return redirect()
            ->route('admin.blog.categories.index')
            ->with('status', "Category \"{$category->name}\" updated successfully.");
    }

    public function destroy(BlogCategory $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            return back()->with('error', "Cannot delete \"{$category->name}\" — it still has posts assigned to it.");
        }

        $this->deleteUpload($category->image);
        $category->delete();

        return back()->with('status', 'Category deleted successfully.');
    }

    public function toggleStatus(BlogCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('status', $category->is_active ? "\"{$category->name}\" enabled." : "\"{$category->name}\" disabled.");
    }

    public function moveUp(BlogCategory $category): RedirectResponse
    {
        $previous = BlogCategory::where('sort_order', '<', $category->sort_order)->orderByDesc('sort_order')->first();

        if ($previous) {
            DB::transaction(function () use ($category, $previous) {
                [$category->sort_order, $previous->sort_order] = [$previous->sort_order, $category->sort_order];
                $category->save();
                $previous->save();
            });
        }

        return back();
    }

    public function moveDown(BlogCategory $category): RedirectResponse
    {
        $next = BlogCategory::where('sort_order', '>', $category->sort_order)->orderBy('sort_order')->first();

        if ($next) {
            DB::transaction(function () use ($category, $next) {
                [$category->sort_order, $next->sort_order] = [$next->sort_order, $category->sort_order];
                $category->save();
                $next->save();
            });
        }

        return back();
    }

    private function validateCategory(Request $request, ?BlogCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function storeUpload($file, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/blogs');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'category-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        $this->deleteUpload($previousFilename);

        return $filename;
    }

    private function deleteUpload(?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('uploads/blogs'.DIRECTORY_SEPARATOR.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
