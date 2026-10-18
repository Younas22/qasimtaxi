<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    /**
     * @var array{html: string, toc: array<int, array{id: string, text: string, level: int}>}|null
     */
    private ?array $parsedContentCache = null;

    protected $fillable = [
        'blog_category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'featured_image_alt',
        'status',
        'is_featured',
        'views_count',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'custom_schema',
        'canonical_override',
        'robots_index',
        'robots_follow',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $post) {
            $post->slug ??= $post->uniqueSlug($post->title);

            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });

        static::updating(function (self $post) {
            if ($post->isDirty('status') && $post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    public function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->when($this->exists, fn ($q) => $q->where('id', '!=', $this->id))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image ? asset('public/uploads/blogs/'.$this->featured_image) : null;
    }

    /**
     * Alt text for the featured image — the admin-written description when
     * set, otherwise the post title as before.
     */
    public function getFeaturedImageAltTextAttribute(): string
    {
        return $this->featured_image_alt ?: $this->title;
    }

    /**
     * Intrinsic pixel size of the featured image (for width/height
     * attributes that prevent layout shift), or null if unreadable.
     *
     * @return array{0: int, 1: int}|null
     */
    public function getFeaturedImageSizeAttribute(): ?array
    {
        $path = $this->featured_image ? public_path('uploads/blogs/'.$this->featured_image) : null;

        if (! $path || ! is_file($path)) {
            return null;
        }

        $size = @getimagesize($path);

        return $size ? [$size[0], $size[1]] : null;
    }

    /**
     * Smaller "-960" variant for responsive srcset, when one was uploaded
     * next to the original.
     */
    public function getFeaturedImageSmallUrlAttribute(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        $small = preg_replace('/(\.[a-z0-9]+)$/i', '-960$1', $this->featured_image);

        return is_file(public_path('uploads/blogs/'.$small)) ? asset('public/uploads/blogs/'.$small) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getExcerptOrSummaryAttribute(): string
    {
        return $this->excerpt ?: Str::limit(strip_tags($this->body), 160);
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->body));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * The post body with `id` attributes injected into every h2/h3 so the
     * table of contents (built from the same headings) can link to them.
     */
    public function getBodyWithHeadingIdsAttribute(): string
    {
        return $this->parsedContent()['html'];
    }

    /**
     * @return array<int, array{id: string, text: string, level: int}>
     */
    public function getTableOfContentsAttribute(): array
    {
        return $this->parsedContent()['toc'];
    }

    /**
     * @return array{html: string, toc: array<int, array{id: string, text: string, level: int}>}
     */
    private function parsedContent(): array
    {
        if ($this->parsedContentCache !== null) {
            return $this->parsedContentCache;
        }

        $body = (string) $this->body;

        if (trim($body) === '') {
            return $this->parsedContentCache = ['html' => $body, 'toc' => []];
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$body.'</body></html>');
        libxml_clear_errors();

        $toc = [];
        $seen = [];

        foreach ((new \DOMXPath($dom))->query('//h2 | //h3') as $heading) {
            $text = trim($heading->textContent);

            if ($text === '') {
                continue;
            }

            $base = Str::slug($text) ?: 'section';
            $slug = $base;
            $i = 1;

            while (isset($seen[$slug])) {
                $slug = $base.'-'.(++$i);
            }

            $seen[$slug] = true;
            $heading->setAttribute('id', $slug);

            $toc[] = [
                'id' => $slug,
                'text' => $text,
                'level' => (int) substr($heading->nodeName, 1),
            ];
        }

        $html = '';
        foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }

        return $this->parsedContentCache = ['html' => $html, 'toc' => $toc];
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at instanceof Carbon && $this->published_at->lessThanOrEqualTo(now());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('views_count');
    }
}
