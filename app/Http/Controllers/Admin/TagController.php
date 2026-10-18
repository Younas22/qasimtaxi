<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount('posts')->orderBy('name')->get();

        return view('admin.blog.tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $tag = Tag::create($data);

        return back()->with('status', "Tag \"{$tag->name}\" added successfully.");
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $data['slug'] = $tag->uniqueSlug($data['name']);

        $tag->update($data);

        return back()->with('status', "Tag \"{$tag->name}\" updated successfully.");
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('status', 'Tag deleted successfully.');
    }
}
