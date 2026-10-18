<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DialCode;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = Setting::current();
        $timezones = timezone_identifiers_list();
        $dialCodes = DialCode::whereNotNull('iso2')->whereNotNull('phone_code')->where('phone_code', '!=', '')->orderBy('name')->get();

        $dateFormats = [
            'Y-m-d' => now()->format('Y-m-d'),
            'd-m-Y' => now()->format('d-m-Y'),
            'm/d/Y' => now()->format('m/d/Y'),
            'd/m/Y' => now()->format('d/m/Y'),
            'M d, Y' => now()->format('M d, Y'),
            'd M, Y' => now()->format('d M, Y'),
            'F j, Y' => now()->format('F j, Y'),
        ];

        return view('admin.settings.edit', compact('settings', 'timezones', 'dateFormats', 'dialCodes'));
    }

    public function update(Request $request): RedirectResponse
    {
        // Admins sometimes paste the whole <iframe> snippet from Google's
        // "Embed a map" dialog instead of just the src URL — pull the URL
        // back out so it still validates and saves correctly.
        if ($request->filled('google_maps_embed_url') && str_contains($request->input('google_maps_embed_url'), '<iframe')) {
            if (preg_match('/src=["\']([^"\']+)["\']/', $request->input('google_maps_embed_url'), $matches)) {
                $request->merge(['google_maps_embed_url' => html_entity_decode($matches[1])]);
            }
        }

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', Rule::in(array_keys(Setting::BUSINESS_TYPES))],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:2048'],
            'logo_dark' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'address' => ['nullable', 'string', 'max:1000'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'google_maps_embed_url' => ['nullable', 'url', 'max:2000'],
            'hours' => ['nullable', 'array'],
            'hours.*.open' => ['nullable', 'date_format:H:i'],
            'hours.*.close' => ['nullable', 'date_format:H:i'],
            'hours.*.closed' => ['nullable', 'boolean'],
            'timezone' => ['required', 'timezone'],
            'default_country' => ['nullable', 'string', 'size:2', 'exists:dial_codes,iso2'],
            'date_format' => ['required', 'string', 'max:20'],
            'tax_label' => ['required', 'string', 'max:50'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_mode' => ['required', 'in:dark,light'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'seo_title_template' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'image', 'max:4096'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'google_analytics_id' => ['nullable', 'string', 'max:50'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'custom_robots_txt' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['invoice_logo_dark'] = $request->boolean('invoice_logo_dark');
        $data['default_robots_index'] = $request->boolean('default_robots_index');
        $data['default_robots_follow'] = $request->boolean('default_robots_follow');

        $data['business_hours'] = collect(Setting::DAYS)->mapWithKeys(fn ($day) => [
            $day => [
                'open' => $data['hours'][$day]['open'] ?? '09:00',
                'close' => $data['hours'][$day]['close'] ?? '18:00',
                'closed' => $request->boolean("hours.{$day}.closed"),
            ],
        ])->all();
        unset($data['hours']);

        $settings = Setting::current();

        // The cached office_lat/office_lng (used by the driver dispatch
        // system) are only valid for the address they were geocoded from —
        // clear them here so OfficeLocationService re-geocodes against the
        // new address the next time it's needed, instead of silently
        // dispatching drivers from the old location.
        if (($data['address'] ?? null) !== $settings->address) {
            $data['office_lat'] = null;
            $data['office_lng'] = null;
        }

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeUpload($request->file('logo'), 'logo', $settings->logo);
        }

        if ($request->hasFile('logo_dark')) {
            $data['logo_dark'] = $this->storeUpload($request->file('logo_dark'), 'logo-dark', $settings->logo_dark);
        }

        if ($request->hasFile('favicon')) {
            $data['favicon'] = $this->storeUpload($request->file('favicon'), 'favicon', $settings->favicon);
        }

        if ($request->hasFile('og_image')) {
            $data['og_image'] = $this->storeUpload($request->file('og_image'), 'og-image', $settings->og_image);
        }

        $settings->update($data);

        return back()->with('status', 'Settings updated successfully.');
    }

    private function storeUpload($file, string $prefix, ?string $previousFilename): string
    {
        $directory = public_path('uploads/settings');

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
