<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    public function create(Page $page): View
    {
        return view('admin.pages.sections.create', compact('page'));
    }

    public function store(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validateSection($request, $page);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'));

            if ($this->isCompactHeroImage($data['type'], $page)) {
                $this->resizeToFit(public_path('uploads/pages/'.$data['image']), 1900, 575);
            }
        }

        if ($request->hasFile('video')) {
            $data['video'] = $this->storeUpload($request->file('video'));
        }

        $data['sort_order'] = $page->sections()->max('sort_order') + 1;
        $data['is_active'] = true;

        $page->sections()->create($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Section added successfully.');
    }

    public function edit(Page $page, PageSection $section): View
    {
        return view('admin.pages.sections.edit', compact('page', 'section'));
    }

    public function update(Request $request, Page $page, PageSection $section): RedirectResponse
    {
        $data = $this->validateSection($request, $page);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), $section->image);

            if ($this->isCompactHeroImage($data['type'], $page)) {
                $this->resizeToFit(public_path('uploads/pages/'.$data['image']), 1900, 575);
            }
        } elseif ($request->boolean('remove_image')) {
            $this->deleteUpload($section->image);
            $data['image'] = null;
        }

        if ($request->hasFile('video')) {
            $data['video'] = $this->storeUpload($request->file('video'), $section->video);
        } elseif ($request->boolean('remove_video')) {
            $this->deleteUpload($section->video);
            $data['video'] = null;
        }

        $section->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Section updated successfully.');
    }

    public function destroy(Page $page, PageSection $section): RedirectResponse
    {
        $this->deleteUpload($section->image);
        $this->deleteUpload($section->video);

        foreach ($section->team_members as $member) {
            $this->deleteUpload($member['photo'] ?? null, 'team');
        }

        $section->delete();

        return back()->with('status', 'Section removed successfully.');
    }

    public function toggleStatus(Page $page, PageSection $section): RedirectResponse
    {
        $section->update(['is_active' => ! $section->is_active]);

        return back()->with('status', $section->is_active ? 'Section enabled.' : 'Section disabled.');
    }

    public function moveUp(Page $page, PageSection $section): RedirectResponse
    {
        $previous = $page->sections()->where('sort_order', '<', $section->sort_order)->orderByDesc('sort_order')->first();

        if ($previous) {
            DB::transaction(function () use ($section, $previous) {
                [$section->sort_order, $previous->sort_order] = [$previous->sort_order, $section->sort_order];
                $section->save();
                $previous->save();
            });
        }

        return back();
    }

    public function moveDown(Page $page, PageSection $section): RedirectResponse
    {
        $next = $page->sections()->where('sort_order', '>', $section->sort_order)->orderBy('sort_order')->first();

        if ($next) {
            DB::transaction(function () use ($section, $next) {
                [$section->sort_order, $next->sort_order] = [$next->sort_order, $section->sort_order];
                $section->save();
                $next->save();
            });
        }

        return back();
    }

    public function reorder(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'section_ids' => ['required', 'array'],
            'section_ids.*' => ['integer', Rule::exists('page_sections', 'id')->where('page_id', $page->id)],
        ]);

        DB::transaction(function () use ($data, $page) {
            foreach ($data['section_ids'] as $index => $sectionId) {
                $page->sections()->where('id', $sectionId)->update(['sort_order' => $index]);
            }
        });

        return back()->with('status', 'Section order updated.');
    }

    private function validateSection(Request $request, Page $page): array
    {
        $type = $request->input('type');

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(PageSection::TYPES))],
            'heading' => ['nullable', 'string', 'max:255'],
            'eyebrow' => ['nullable', 'string', 'max:60'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'differentiator' => ['nullable', 'string', 'max:160'],
            'body' => [Rule::requiredIf($type === 'rich_text'), 'nullable', 'string'],
            // No dimension cap here — a photo an admin actually has almost
            // never comes pre-cropped to the service-page hero's exact
            // 1900×575 banner shape, and rejecting the upload outright over
            // that (as this used to) just blocks every real photo. Instead
            // storeUpload() downscales the saved file to fit afterwards —
            // see isCompactHeroImage() below.
            'image' => ['nullable', 'image', 'max:4096'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:25600'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'button_text_2' => ['nullable', 'string', 'max:100'],
            'button_url_2' => ['nullable', 'string', 'max:255'],
            'hero_buttons' => ['nullable', 'array'],
            'hero_buttons.*.id' => ['nullable', 'string', 'max:100'],
            'hero_buttons.*.kind' => ['required_with:hero_buttons', Rule::in(['book_now', 'call_now', 'phone_number', 'price', 'custom'])],
            'hero_buttons.*.label' => ['nullable', 'string', 'max:100'],
            'hero_buttons.*.url' => ['nullable', 'string', 'max:255'],
            'hero_buttons.*.hero_show_desktop' => ['nullable', 'boolean'],
            'hero_buttons.*.hero_show_mobile' => ['nullable', 'boolean'],
            'hero_buttons.*.navbar_show_desktop' => ['nullable', 'boolean'],
            'hero_buttons.*.navbar_show_mobile' => ['nullable', 'boolean'],
            'hero_buttons.*.bg_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'hero_buttons.*.text_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'items' => [Rule::requiredIf(in_array($type, ['items', 'trust_badges', 'faq', 'stats', 'team', 'process'], true)), 'nullable', 'array'],
            'items.*.title' => ['required_if:type,items', 'required_if:type,trust_badges', 'required_if:type,process', 'nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.icon' => ['nullable', 'string', 'max:50'],
            'items.*.link' => ['nullable', 'string', 'max:255'],
            'items.*.question' => ['required_if:type,faq', 'nullable', 'string', 'max:255'],
            'items.*.answer' => ['required_if:type,faq', 'nullable', 'string', 'max:1000'],
            'items.*.category' => ['nullable', 'string', 'max:100'],
            'items.*.label' => ['required_if:type,stats', 'nullable', 'string', 'max:100'],
            'items.*.value' => ['required_if:type,stats', 'nullable', 'integer', 'min:0', 'max:999999999'],
            'items.*.suffix' => ['nullable', 'string', 'max:10'],
            'items.*.name' => ['required_if:type,team', 'nullable', 'string', 'max:255'],
            'items.*.role' => ['nullable', 'string', 'max:100'],
            'items.*.bio' => ['nullable', 'string', 'max:500'],
            'items.*.photo' => ['nullable', 'image', 'max:2048'],
            'items.*.existing_photo' => ['nullable', 'string'],
            'testimonial_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'testimonial_min_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'fleet_limit' => ['nullable', 'integer', 'min:1', 'max:48'],
            'fleet_category_id' => ['nullable', 'integer', 'exists:vehicle_categories,id'],
            'route_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'blog_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'vision_icon' => ['nullable', 'string', 'max:50'],
            'vision_title' => ['nullable', 'string', 'max:255'],
            'vision_body' => ['nullable', 'string', 'max:1000'],
            'mission_icon' => ['nullable', 'string', 'max:50'],
            'mission_title' => ['nullable', 'string', 'max:255'],
            'mission_body' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['content'] = match ($type) {
            'items' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['title'] ?? null))
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? null,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'link' => $item['link'] ?? null,
                ])->values()->all(),
            'trust_badges' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['title'] ?? null))
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? null,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'link' => $item['link'] ?? null,
                ])->values()->all(),
            'faq' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['question'] ?? null))
                ->map(fn ($item) => [
                    'question' => $item['question'],
                    'answer' => $item['answer'] ?? null,
                    'category' => $item['category'] ?? null,
                ])->values()->all(),
            'process' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['title'] ?? null))
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? null,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                ])->values()->all(),
            // All contact details (address/phone/email/whatsapp/hours/map
            // embed) now live in Settings > General — the single source of
            // truth shared by the footer, WhatsApp button, invoices, and
            // this page. This section is just a placeholder marking where
            // that block renders on the Contact page.
            'contact_info' => [],
            'hero' => [
                'buttons' => collect($data['hero_buttons'] ?? [])
                    ->filter(fn ($button) => filled($button['kind'] ?? null))
                    ->map(fn ($button, $index) => [
                        'id' => filled($button['id'] ?? null) ? $button['id'] : ('button-'.$index.'-'.Str::random(6)),
                        'kind' => $button['kind'],
                        'label' => $button['label'] ?? null,
                        'url' => $button['url'] ?? null,
                        'hero_show_desktop' => (bool) ($button['hero_show_desktop'] ?? false),
                        'hero_show_mobile' => (bool) ($button['hero_show_mobile'] ?? false),
                        'navbar_show_desktop' => (bool) ($button['navbar_show_desktop'] ?? false),
                        'navbar_show_mobile' => (bool) ($button['navbar_show_mobile'] ?? false),
                        'bg_color' => $button['bg_color'] ?: null,
                        'text_color' => $button['text_color'] ?: null,
                    ])->values()->all(),
            ],
            'testimonials' => [
                'limit' => (int) ($data['testimonial_limit'] ?? 6),
                'min_rating' => (int) ($data['testimonial_min_rating'] ?? 4),
            ],
            'fleet' => [
                'limit' => (int) ($data['fleet_limit'] ?? 12),
                'category_id' => $data['fleet_category_id'] ?? null,
            ],
            'routes' => [
                'limit' => (int) ($data['route_limit'] ?? 6),
            ],
            // The list of towns/cities itself is managed at Admin → Service
            // Areas, not per-section — this just marks where it renders.
            'areas' => [],
            'blog' => [
                'limit' => (int) ($data['blog_limit'] ?? 6),
            ],
            'stats' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['label'] ?? null))
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? null,
                    'label' => $item['label'],
                    'value' => (int) ($item['value'] ?? 0),
                    'suffix' => $item['suffix'] ?? null,
                ])->values()->all(),
            'vision_mission' => [
                'vision_icon' => $data['vision_icon'] ?? null,
                'vision_title' => $data['vision_title'] ?? null,
                'vision_body' => $data['vision_body'] ?? null,
                'mission_icon' => $data['mission_icon'] ?? null,
                'mission_title' => $data['mission_title'] ?? null,
                'mission_body' => $data['mission_body'] ?? null,
            ],
            'team' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['name'] ?? null))
                ->map(function ($item) {
                    $photo = $item['existing_photo'] ?? null;

                    if (isset($item['photo']) && $item['photo'] instanceof \Illuminate\Http\UploadedFile) {
                        $photo = $this->storeUpload($item['photo'], $photo, 'team');
                    }

                    return [
                        'photo' => $photo,
                        'name' => $item['name'],
                        'role' => $item['role'] ?? null,
                        'bio' => $item['bio'] ?? null,
                    ];
                })->values()->all(),
            default => null,
        };

        unset(
            $data['items'], $data['hero_buttons'],
            $data['testimonial_limit'], $data['testimonial_min_rating'], $data['fleet_limit'], $data['fleet_category_id'], $data['route_limit'], $data['blog_limit'],
            $data['vision_icon'], $data['vision_title'], $data['vision_body'],
            $data['mission_icon'], $data['mission_title'], $data['mission_body']
        );

        return $data;
    }

    /**
     * Only a service page's hero renders as the compact 1900×575 banner
     * strip (see pages/sections/hero.blade.php) — the home page's hero is a
     * full-screen cinematic background and the CTA section's image fills a
     * ~560-620px-tall card, so neither should ever be downscaled to fit a
     * short banner.
     */
    private function isCompactHeroImage(string $type, Page $page): bool
    {
        return $type === 'hero' && Page::isServiceLike($page->slug);
    }

    /**
     * Downscales an already-saved image in place to fit within the given box,
     * preserving aspect ratio — never upscales, so a source already smaller
     * than the box is left untouched. Uses GD (bundled with PHP) since the
     * project has no image-processing package installed.
     */
    private function resizeToFit(string $path, int $maxWidth, int $maxHeight): void
    {
        $info = @getimagesize($path);

        if (! $info) {
            return;
        }

        [$width, $height, $imageType] = $info;
        $scale = min($maxWidth / $width, $maxHeight / $height, 1.0);

        if ($scale >= 1.0) {
            return;
        }

        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $source = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => null,
        };

        if (! $source) {
            return;
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for formats that support it, instead of it
        // rendering as opaque black.
        if (in_array($imageType, [IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        match ($imageType) {
            IMAGETYPE_JPEG => imagejpeg($resized, $path, 90),
            IMAGETYPE_PNG => imagepng($resized, $path),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($resized, $path, 90) : null,
            IMAGETYPE_GIF => imagegif($resized, $path),
            default => null,
        };

        imagedestroy($source);
        imagedestroy($resized);
    }

    private function storeUpload($file, ?string $previousFilename = null, string $directory = 'pages'): string
    {
        $path = public_path('uploads/'.$directory);

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $filename = 'section-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($path, $filename);

        $this->deleteUpload($previousFilename, $directory);

        return $filename;
    }

    private function deleteUpload(?string $filename, string $directory = 'pages'): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('uploads/'.$directory.DIRECTORY_SEPARATOR.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
