<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::with(['category', 'author', 'tags'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('blog_category_id', $request->query('category')))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->query('search').'%'))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $categories = BlogCategory::ordered()->get();

        return view('admin.blog.posts.index', compact('posts', 'categories'));
    }

    public function create(): View
    {
        return view('admin.blog.posts.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePost($request);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->storeUpload($request->file('featured_image'));
        }

        $data['author_id'] = Auth::guard('admin')->id();

        $post = BlogPost::create($data);
        $post->tags()->sync($this->resolveTags($request));

        return redirect()
            ->route('admin.blog.index')
            ->with('status', "Post \"{$post->title}\" created successfully.");
    }

    public function edit(BlogPost $post): View
    {
        $post->load('tags');

        return view('admin.blog.posts.edit', $this->formOptions() + ['post' => $post]);
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $this->validatePost($request, $post);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->storeUpload($request->file('featured_image'), $post->featured_image);
        } elseif ($request->boolean('remove_featured_image')) {
            $this->deleteUpload($post->featured_image);
            $data['featured_image'] = null;
        }

        $post->update($data);
        $post->tags()->sync($this->resolveTags($request));

        return redirect()
            ->route('admin.blog.index')
            ->with('status', "Post \"{$post->title}\" updated successfully.");
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $this->deleteUpload($post->featured_image);
        $post->delete();

        return back()->with('status', 'Post deleted successfully.');
    }

    public function toggleFeatured(BlogPost $post): RedirectResponse
    {
        $post->update(['is_featured' => ! $post->is_featured]);

        return back()->with('status', $post->is_featured ? "\"{$post->title}\" marked as featured." : "\"{$post->title}\" removed from featured.");
    }

    private function formOptions(): array
    {
        return [
            'categories' => BlogCategory::ordered()->get(),
            'tags' => Tag::orderBy('name')->pluck('name'),
            'statuses' => BlogPost::STATUSES,
        ];
    }

    private function validatePost(Request $request, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(BlogPost::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_override' => ['nullable', 'url', 'max:255'],
            'custom_schema' => ['nullable', 'string'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['robots_index'] = $request->boolean('robots_index', $post === null);
        $data['robots_follow'] = $request->boolean('robots_follow', $post === null);
        $data['slug'] = ($post ?? new BlogPost())->uniqueSlug(filled($data['slug'] ?? null) ? $data['slug'] : $data['title']);

        if (blank($data['published_at'] ?? null)) {
            unset($data['published_at']);
        }

        return $data;
    }

    /**
     * @return array<int, int>
     */
    private function resolveTags(Request $request): array
    {
        $names = array_filter(array_map('trim', explode(',', (string) $request->input('tags', ''))));

        return Tag::resolveByNames($names)->pluck('id')->all();
    }

    private function storeUpload($file, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/blogs');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'post-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
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
