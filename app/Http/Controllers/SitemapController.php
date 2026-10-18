<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        // The site-wide indexing toggle is a kill switch (see
        // layouts/public.blade.php) — no point listing anything here that
        // every page is now telling crawlers to skip.
        $indexingEnabled = setting('default_robots_index', true);

        if (! $indexingEnabled) {
            $pages = $areas = $posts = $categories = $tags = collect();
        } else {
            $pages = Page::where('is_active', true)->where('robots_index', true)->get();

            $areas = Area::active()->where('robots_index', true)->get(['slug', 'updated_at']);

            $posts = BlogPost::published()->where('robots_index', true)->get(['slug', 'updated_at']);

            $categories = BlogCategory::active()
                ->whereHas('posts', fn ($q) => $q->published())
                ->get(['slug', 'updated_at']);

            $tags = \App\Models\Tag::whereHas('posts', fn ($q) => $q->published())->get(['slug', 'updated_at']);
        }

        $xml = view('sitemap.index', compact('pages', 'areas', 'posts', 'categories', 'tags', 'indexingEnabled'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        // An admin-authored robots.txt (Settings → SEO) always wins outright
        // — once they've taken the wheel here, nothing below should second-
        // guess it.
        if (filled(setting('custom_robots_txt'))) {
            return response(setting('custom_robots_txt'), 200)->header('Content-Type', 'text/plain');
        }

        return response($this->defaultRobotsTxt(), 200)->header('Content-Type', 'text/plain');
    }

    /**
     * The auto-generated default — also used to show the admin a live
     * preview of what /robots.txt currently serves when they haven't
     * written a custom one.
     */
    public function defaultRobotsTxt(): string
    {
        // Same site-wide kill switch as the sitemap and the <meta robots>
        // tag (see layouts/public.blade.php) — if indexing is off, tell
        // every crawler to stay out entirely rather than leaving a
        // half-true robots.txt that still invites them in.
        if (! setting('default_robots_index', true)) {
            return implode("\n", [
                'User-agent: *',
                'Disallow: /',
            ]);
        }

        return implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /booking/invoice',
            // Private, per-customer pages — confirmations, payment links,
            // customer/driver panels. CSS/JS/images stay crawlable.
            'Disallow: /booking/confirmation',
            'Disallow: /booking/pay',
            'Disallow: /account',
            'Disallow: /driver',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ]);
    }
}
