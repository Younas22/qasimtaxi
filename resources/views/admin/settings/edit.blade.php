<x-admin.layouts.app :title="__('Global Settings')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Global Settings') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Control your company profile, branding and regional preferences.') }}</p>
    </div>

    @php
        // Land on whichever tab has the failed field(s) after a validation
        // error, so the admin isn't left staring at a blank-looking save
        // with no visible clue why it didn't go through.
        $errorTab = 'general';
        if ($errors->hasAny(['logo', 'logo_dark', 'favicon', 'invoice_logo_dark', 'primary_color', 'secondary_color', 'accent_color', 'text_color', 'background_color', 'theme_mode'])) {
            $errorTab = 'branding';
        } elseif ($errors->hasAny(['email', 'phone', 'whatsapp', 'address', 'google_maps_embed_url']) || $errors->has('hours.*')) {
            $errorTab = 'contact';
        } elseif ($errors->hasAny(['tax_label', 'tax_rate'])) {
            $errorTab = 'billing';
        } elseif ($errors->hasAny(['meta_title', 'meta_description', 'meta_keywords', 'seo_title_template', 'og_image', 'google_site_verification', 'google_analytics_id'])) {
            $errorTab = 'seo';
        } elseif ($errors->hasAny(['facebook_url', 'instagram_url', 'twitter_url', 'linkedin_url', 'youtube_url'])) {
            $errorTab = 'social';
        }
    @endphp

    <div x-data="{ tab: '{{ $errorTab }}' }">
        {{-- Tabs --}}
        <div class="scrollbar-luxury mb-6 flex gap-1 overflow-x-auto border-b border-luxury-border">
            @foreach ([
                ['key' => 'general', 'label' => __('General'), 'icon' => 'briefcase'],
                ['key' => 'branding', 'label' => __('Branding'), 'icon' => 'sparkles'],
                ['key' => 'contact', 'label' => __('Contact & Hours'), 'icon' => 'phone'],
                ['key' => 'billing', 'label' => __('Billing'), 'icon' => 'credit-card'],
                ['key' => 'seo', 'label' => __('SEO'), 'icon' => 'search'],
                ['key' => 'social', 'label' => __('Social'), 'icon' => 'link'],
            ] as $navTab)
                <button type="button" @click="tab = '{{ $navTab['key'] }}'"
                    :class="tab === '{{ $navTab['key'] }}' ? 'border-luxury-gold text-luxury-gold' : 'border-transparent text-luxury-muted hover:text-luxury-white'"
                    class="tap-scale flex shrink-0 items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition">
                    <x-icon name="{{ $navTab['icon'] }}" class="h-4 w-4" />
                    {{ $navTab['label'] }}
                </button>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- General --}}
            <div x-show="tab === 'general'" x-cloak class="space-y-6">
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Company Information') }}</h3>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-admin.input-label for="company_name" value="{{ __('Company Name') }}" />
                            <x-admin.text-input id="company_name" name="company_name" type="text" value="{{ old('company_name', $settings->company_name) }}" required autofocus />
                            <x-admin.input-error :messages="$errors->get('company_name')" />
                        </div>

                        <div>
                            <x-admin.input-label for="tagline" value="{{ __('Tagline') }}" />
                            <x-admin.text-input id="tagline" name="tagline" type="text" value="{{ old('tagline', $settings->tagline) }}" />
                            <x-admin.input-error :messages="$errors->get('tagline')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-admin.input-label for="business_type" value="{{ __('Business Type') }}" />
                            <select id="business_type" name="business_type"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                                @foreach (\App\Models\Setting::BUSINESS_TYPES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('business_type', $settings->business_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-luxury-muted">{{ __('Used to pick the right structured-data (schema.org) type for your website — helps Google understand what kind of business you are.') }}</p>
                            <x-admin.input-error :messages="$errors->get('business_type')" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Regional') }}</h3>

                    <div class="mb-5">
                        <x-admin.input-label for="timezone" value="{{ __('Timezone') }}" />
                        <select id="timezone" name="timezone" required
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $settings->timezone) === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                        <x-admin.input-error :messages="$errors->get('timezone')" />
                    </div>

                    <div class="mb-5">
                        <x-admin.input-label for="default_country" value="{{ __('Default Country') }}" />
                        <select id="default_country" name="default_country"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($dialCodes as $dialCode)
                                <option value="{{ $dialCode->iso2 }}" @selected(old('default_country', $settings->default_country) === $dialCode->iso2)>{{ $dialCode->name }} ({{ $dialCode->dial }})</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Sets the default phone country code on the public booking form.') }}</p>
                        <x-admin.input-error :messages="$errors->get('default_country')" />
                    </div>

                    <div>
                        <x-admin.input-label value="{{ __('Date Format') }}" />
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($dateFormats as $format => $example)
                                <label class="cursor-pointer rounded-lg border px-3 py-2.5 text-center text-sm transition has-[:checked]:border-luxury-gold has-[:checked]:bg-luxury-gold/10 has-[:checked]:text-luxury-gold {{ old('date_format', $settings->date_format) === $format ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted' }}">
                                    <input type="radio" name="date_format" value="{{ $format }}" class="hidden" @checked(old('date_format', $settings->date_format) === $format)>
                                    {{ $example }}
                                </label>
                            @endforeach
                        </div>
                        <x-admin.input-error :messages="$errors->get('date_format')" />
                    </div>
                </div>
            </div>

            {{-- Branding --}}
            <div x-show="tab === 'branding'" x-cloak class="space-y-6">
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Branding') }}</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div x-data="{ preview: '{{ $settings->logo_url }}' }" class="rounded-xl border border-luxury-border bg-luxury-slate p-4">
                            <x-admin.input-label value="{{ __('Logo (White)') }}" />
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-luxury-border bg-luxury-black">
                                    <template x-if="preview">
                                        <img :src="preview" alt="{{ __('White logo preview') }}" class="h-full w-full object-contain">
                                    </template>
                                    <template x-if="!preview">
                                        <span class="text-xs text-luxury-muted">{{ __('No logo') }}</span>
                                    </template>
                                </div>
                                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    <span>{{ __('Click to upload logo') }}</span>
                                    <input type="file" name="logo" accept="image/*,.svg" class="hidden"
                                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-luxury-muted">{{ __('For dark backgrounds — site header, admin panel. PNG or SVG recommended. Max 2MB.') }}</p>
                            <x-admin.input-error :messages="$errors->get('logo')" />
                        </div>

                        <div x-data="{ preview: '{{ $settings->logo_dark_url }}' }" class="rounded-xl border border-luxury-border bg-luxury-slate p-4">
                            <x-admin.input-label value="{{ __('Logo (Black)') }}" />
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-luxury-border bg-white">
                                    <template x-if="preview">
                                        <img :src="preview" alt="{{ __('Black logo preview') }}" class="h-full w-full object-contain">
                                    </template>
                                    <template x-if="!preview">
                                        <span class="text-xs text-luxury-muted">{{ __('No logo') }}</span>
                                    </template>
                                </div>
                                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    <span>{{ __('Click to upload logo') }}</span>
                                    <input type="file" name="logo_dark" accept="image/*,.svg" class="hidden"
                                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-luxury-muted">{{ __('For light backgrounds — invoices, printed documents. PNG or SVG recommended. Max 2MB.') }}</p>
                            <x-admin.input-error :messages="$errors->get('logo_dark')" />
                        </div>

                        <div x-data="{ preview: '{{ $settings->favicon_url }}' }" class="rounded-xl border border-luxury-border bg-luxury-slate p-4">
                            <x-admin.input-label value="{{ __('Favicon') }}" />
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite">
                                    <template x-if="preview">
                                        <img :src="preview" alt="{{ __('Favicon preview') }}" class="h-8 w-8 object-contain">
                                    </template>
                                    <template x-if="!preview">
                                        <span class="text-xs text-luxury-muted">{{ __('No favicon') }}</span>
                                    </template>
                                </div>
                                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    <span>{{ __('Click to upload favicon') }}</span>
                                    <input type="file" name="favicon" accept="image/*" class="hidden"
                                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-luxury-muted">{{ __('Square PNG or ICO recommended. Max 512KB.') }}</p>
                            <x-admin.input-error :messages="$errors->get('favicon')" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-admin.toggle name="invoice_logo_dark" :checked="old('invoice_logo_dark', $settings->invoice_logo_dark)"
                            label="{{ __('Use Black Logo on Invoices') }}" description="{{ __('Off = White logo shown on invoices. On = Black logo shown instead.') }}" />
                    </div>
                </div>

                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Theme') }}</h3>
                    <p class="mb-4 text-xs text-luxury-muted">{{ __('Colors apply to the public website immediately after saving — no rebuild required. The admin panel and customer portal keep their own fixed colors and are not affected.') }}</p>

                    <div class="mb-6">
                        <x-admin.input-label value="{{ __('Appearance Mode') }}" />
                        <div class="grid grid-cols-2 gap-3 sm:max-w-sm">
                            <label class="cursor-pointer rounded-lg border px-4 py-3 text-center text-sm transition has-[:checked]:border-luxury-gold has-[:checked]:bg-luxury-gold/10 has-[:checked]:text-luxury-gold {{ old('theme_mode', $settings->theme_mode) === 'dark' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted' }}">
                                <input type="radio" name="theme_mode" value="dark" class="hidden" @checked(old('theme_mode', $settings->theme_mode) === 'dark')>
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                    </svg>
                                    {{ __('Dark Mode') }}
                                </span>
                            </label>

                            <label class="cursor-pointer rounded-lg border px-4 py-3 text-center text-sm transition has-[:checked]:border-luxury-gold has-[:checked]:bg-luxury-gold/10 has-[:checked]:text-luxury-gold {{ old('theme_mode', $settings->theme_mode) === 'light' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted' }}">
                                <input type="radio" name="theme_mode" value="light" class="hidden" @checked(old('theme_mode', $settings->theme_mode) === 'light')>
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                    </svg>
                                    {{ __('Light Mode') }}
                                </span>
                            </label>
                        </div>
                        <x-admin.input-error :messages="$errors->get('theme_mode')" />
                        <p class="mt-2 text-xs text-luxury-muted">{{ __('Tip: pick matching Background & Text colors below for best contrast when switching modes.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <x-admin.color-input name="primary_color" label="{{ __('Primary Color') }}" :value="old('primary_color', $settings->primary_color)" />
                        <x-admin.color-input name="secondary_color" label="{{ __('Secondary Color') }}" :value="old('secondary_color', $settings->secondary_color)" />
                        <x-admin.color-input name="accent_color" label="{{ __('Accent Color') }}" :value="old('accent_color', $settings->accent_color)" />
                        <x-admin.color-input name="text_color" label="{{ __('Text Color') }}" :value="old('text_color', $settings->text_color)" />
                        <x-admin.color-input name="background_color" label="{{ __('Background Color') }}" :value="old('background_color', $settings->background_color)" />
                    </div>
                    <x-admin.input-error :messages="$errors->get('primary_color')" class="mt-3" />
                    <x-admin.input-error :messages="$errors->get('secondary_color')" class="mt-1" />
                    <x-admin.input-error :messages="$errors->get('accent_color')" class="mt-1" />
                    <x-admin.input-error :messages="$errors->get('text_color')" class="mt-1" />
                    <x-admin.input-error :messages="$errors->get('background_color')" class="mt-1" />
                </div>
            </div>

            {{-- Contact & Hours --}}
            <div x-show="tab === 'contact'" x-cloak class="space-y-6">
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Contact Details') }}</h3>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <x-admin.input-label for="email" value="{{ __('Email Address') }}" />
                            <x-admin.text-input id="email" name="email" type="email" value="{{ old('email', $settings->email) }}" />
                            <x-admin.input-error :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-admin.input-label for="phone" value="{{ __('Phone') }}" />
                            <x-admin.text-input id="phone" name="phone" type="text" value="{{ old('phone', $settings->phone) }}" />
                            <x-admin.input-error :messages="$errors->get('phone')" />
                        </div>

                        <div>
                            <x-admin.input-label for="whatsapp" value="{{ __('WhatsApp Number') }}" />
                            <x-admin.text-input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $settings->whatsapp) }}" />
                            <x-admin.input-error :messages="$errors->get('whatsapp')" />
                        </div>
                    </div>

                    <div class="mt-5">
                        <x-admin.input-label for="address" value="{{ __('Address') }}" />
                        <textarea id="address" name="address" rows="3"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('address', $settings->address) }}</textarea>
                        <x-admin.input-error :messages="$errors->get('address')" />
                    </div>

                    <div class="mt-5">
                        <x-admin.input-label for="google_maps_embed_url" value="{{ __('Google Maps Embed URL (optional)') }}" />
                        <x-admin.text-input id="google_maps_embed_url" name="google_maps_embed_url" type="url" value="{{ old('google_maps_embed_url', $settings->google_maps_embed_url) }}" placeholder="https://www.google.com/maps/embed?..." />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('From Google Maps: Share → Embed a map → copy the "src" URL (or paste the whole embed code). Shown on the Contact page. Leave blank to show a placeholder.') }}</p>
                        <x-admin.input-error :messages="$errors->get('google_maps_embed_url')" />
                    </div>
                </div>

                @php
                    $homePage = \App\Models\Page::where('slug', 'home')->first();
                    $homeHeroSection = $homePage?->sections()->where('type', 'hero')->first();
                @endphp
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Top Navbar Buttons') }}</h3>
                    <p class="text-xs text-luxury-muted">
                        {{ __('Book Now, Call Now / your phone number, Pricing, and any custom links — for both the top navbar and the homepage hero — are managed together in one place:') }}
                        @if ($homePage && $homeHeroSection)
                            <a href="{{ route('admin.pages.sections.edit', [$homePage, $homeHeroSection]) }}" class="text-luxury-gold hover:text-luxury-gold-light">
                                {{ __('Home → Hero section') }}
                            </a>.
                        @else
                            {{ __('Home → Hero section.') }}
                        @endif
                        {{ __('Each button there has its own Desktop/Mobile toggle for the hero and, separately, for the navbar.') }}
                    </p>
                </div>

                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Business Hours') }}</h3>
                    <p class="mb-4 text-xs text-luxury-muted">{{ __('Set your opening hours for each day, shown on the Contact page.') }}</p>

                    <div class="space-y-2">
                        @php $businessHours = old('hours', $settings->business_hours_list); @endphp
                        @foreach (\App\Models\Setting::DAYS as $day)
                            @php $dayHours = $businessHours[$day] ?? ['open' => '09:00', 'close' => '18:00', 'closed' => false]; @endphp
                            <div class="flex flex-wrap items-center gap-4 rounded-xl border border-luxury-border/60 bg-luxury-graphite/30 px-4 py-3"
                                x-data="{ dayOpen: {{ \Illuminate\Support\Js::from(! ($dayHours['closed'] ?? false)) }} }">
                                <p class="w-28 shrink-0 text-sm font-medium text-luxury-white">{{ __(ucfirst($day)) }}</p>

                                <label class="flex shrink-0 cursor-pointer items-center gap-2.5">
                                    <span class="relative inline-flex items-center">
                                        <input type="checkbox" x-model="dayOpen" class="peer sr-only">
                                        <span class="h-6 w-11 rounded-full bg-luxury-slate transition peer-checked:bg-luxury-gold peer-focus-visible:ring-2 peer-focus-visible:ring-luxury-gold peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-luxury-black"></span>
                                        <span class="absolute start-1 top-1 h-4 w-4 rounded-full bg-luxury-white transition-transform peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                                    </span>
                                    <span class="text-xs font-medium" :class="dayOpen ? 'text-luxury-gold' : 'text-luxury-muted'" x-text="dayOpen ? '{{ __('Open') }}' : '{{ __('Closed') }}'"></span>
                                </label>
                                <input type="hidden" name="hours[{{ $day }}][closed]" :value="dayOpen ? '' : '1'">

                                <div class="flex flex-1 items-center gap-2" x-show="dayOpen">
                                    <input type="time" name="hours[{{ $day }}][open]" value="{{ $dayHours['open'] ?? '09:00' }}" :disabled="!dayOpen"
                                        class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                                    <span class="text-xs text-luxury-muted">{{ __('to') }}</span>
                                    <input type="time" name="hours[{{ $day }}][close]" value="{{ $dayHours['close'] ?? '18:00' }}" :disabled="!dayOpen"
                                        class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                                </div>
                                <p class="flex-1 text-xs text-luxury-muted" x-show="!dayOpen" x-cloak>{{ __('Closed all day') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Billing --}}
            <div x-show="tab === 'billing'" x-cloak class="space-y-6">
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Invoice & Tax') }}</h3>
                    <p class="mb-4 text-xs text-luxury-muted">{{ __('Applied as a tax-inclusive breakdown on booking invoices and payment receipts.') }}</p>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-admin.input-label for="tax_label" value="{{ __('Tax Label') }}" />
                            <x-admin.text-input id="tax_label" name="tax_label" type="text" class="w-full" :value="old('tax_label', $settings->tax_label)" required />
                            <x-admin.input-error :messages="$errors->get('tax_label')" />
                        </div>
                        <div>
                            <x-admin.input-label for="tax_rate" value="{{ __('Tax Rate (%)') }}" />
                            <x-admin.text-input id="tax_rate" name="tax_rate" type="number" step="0.01" min="0" max="100" class="w-full" :value="old('tax_rate', $settings->tax_rate)" required />
                            <x-admin.input-error :messages="$errors->get('tax_rate')" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div x-show="tab === 'seo'" x-cloak class="space-y-6">
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('SEO') }}</h3>
                    <p class="mb-4 text-xs text-luxury-muted">{{ __("Default meta tags used site-wide when an individual page doesn't set its own.") }}</p>

                    <div class="mb-5">
                        <x-admin.input-label for="meta_title" value="{{ __('Default Meta Title') }}" />
                        <x-admin.text-input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $settings->meta_title) }}" placeholder="{{ __('e.g. Premium Chauffeur & Limousine Service') }}" />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __("Used for the homepage and any page that doesn't set its own Meta Title.") }}</p>
                        <x-admin.input-error :messages="$errors->get('meta_title')" />
                    </div>

                    <div class="mb-5">
                        <x-admin.input-label for="seo_title_template" value="{{ __('Page Title Format') }}" />
                        <x-admin.text-input id="seo_title_template" name="seo_title_template" type="text" value="{{ old('seo_title_template', $settings->seo_title_template) }}" placeholder="{page_title} | {business_name}" />
                        <p class="mt-1 text-xs text-luxury-muted">
                            {{ __('Controls how every page\'s browser-tab title is built. Use {page_title} and {business_name} as placeholders — e.g. "Airport Transfer | :name". If a page\'s own title already includes your business name, it\'s never added twice.', ['name' => $settings->company_name]) }}
                        </p>
                        <x-admin.input-error :messages="$errors->get('seo_title_template')" />
                    </div>

                    <div class="mb-5">
                        <x-admin.input-label for="meta_description" value="{{ __('Default Meta Description') }}" />
                        <textarea id="meta_description" name="meta_description" rows="2" maxlength="500"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('meta_description', $settings->meta_description) }}</textarea>
                        <x-admin.input-error :messages="$errors->get('meta_description')" />
                    </div>

                    <div class="mb-5">
                        <x-admin.input-label for="meta_keywords" value="{{ __('Meta Keywords (optional, comma-separated)') }}" />
                        <x-admin.text-input id="meta_keywords" name="meta_keywords" type="text" value="{{ old('meta_keywords', $settings->meta_keywords) }}" placeholder="chauffeur service, limousine rental, airport transfer" />
                        <x-admin.input-error :messages="$errors->get('meta_keywords')" />
                    </div>

                    <div class="mb-5" x-data="{ preview: '{{ $settings->og_image_url }}' }">
                        <x-admin.input-label value="{{ __('Default Social Share Image (Open Graph)') }}" />
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                                <template x-if="preview">
                                    <img :src="preview" alt="{{ __('OG image preview') }}" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!preview">
                                    <span class="text-[10px] text-luxury-muted">{{ __('No image') }}</span>
                                </template>
                            </div>
                            <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                <span>{{ __('Click to upload image') }}</span>
                                <input type="file" name="og_image" accept="image/*" class="hidden"
                                    @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-luxury-muted">{{ __('Shown when pages are shared on social media. Recommended 1200×630px. Falls back to your logo if left blank.') }}</p>
                        <x-admin.input-error :messages="$errors->get('og_image')" />
                    </div>

                    <div class="mb-5">
                        <x-admin.input-label for="google_site_verification" value="{{ __('Google Search Console Verification Code (optional)') }}" />
                        <x-admin.text-input id="google_site_verification" name="google_site_verification" type="text" value="{{ old('google_site_verification', $settings->google_site_verification) }}" placeholder="e.g. abc123XYZ..." />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __("Paste just the content value from Google's verification meta tag.") }}</p>
                        <x-admin.input-error :messages="$errors->get('google_site_verification')" />
                    </div>

                    <div>
                        <x-admin.input-label for="google_analytics_id" value="{{ __('Google Analytics Measurement ID (optional)') }}" />
                        <x-admin.text-input id="google_analytics_id" name="google_analytics_id" type="text" value="{{ old('google_analytics_id', $settings->google_analytics_id) }}" placeholder="e.g. G-XXXXXXXXXX" />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Your GA4 Measurement ID. The tracking code is added to every page automatically once saved.') }}</p>
                        <x-admin.input-error :messages="$errors->get('google_analytics_id')" />
                    </div>
                </div>

                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Search Engine Indexing') }}</h3>
                    <p class="mb-4 text-xs text-luxury-muted">{{ __("Default behavior for any page, area, or blog post that doesn't set its own — individual pages can still override this.") }}</p>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <x-admin.toggle name="default_robots_index" :checked="old('default_robots_index', $settings->default_robots_index)"
                            label="{{ __('Allow search engines to index new pages') }}" description="{{ __('Off = the entire site tells crawlers to stay out (robots.txt and every page\'s meta tag) — individual pages can still be indexed on top of this being on, but nothing overrides it being off.') }}" />
                        <x-admin.toggle name="default_robots_follow" :checked="old('default_robots_follow', $settings->default_robots_follow)"
                            label="{{ __('Allow search engines to follow links') }}" description="{{ __('Leave this on unless you have a specific reason to turn it off.') }}" />
                    </div>
                </div>

                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('robots.txt') }}</h3>
                    <p class="mb-4 text-xs text-luxury-muted">
                        {{ __('Served live at :url. Leave blank to use the automatically generated version below, which already respects the indexing toggle above.', ['url' => url('/robots.txt')]) }}
                    </p>

                    <x-admin.input-label for="custom_robots_txt" value="{{ __('Custom robots.txt (optional)') }}" />
                    <textarea id="custom_robots_txt" name="custom_robots_txt" rows="6" placeholder="{{ app(\App\Http\Controllers\SitemapController::class)->defaultRobotsTxt() }}"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 font-mono text-xs text-luxury-white placeholder:text-luxury-muted/60 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('custom_robots_txt', $settings->custom_robots_txt) }}</textarea>
                    <p class="mt-1 text-xs text-luxury-muted">{{ __('Filling this in replaces the generated version completely, including the sitemap line — add it back yourself if you still want it.') }}</p>
                    <x-admin.input-error :messages="$errors->get('custom_robots_txt')" />
                </div>
            </div>

            {{-- Social --}}
            <div x-show="tab === 'social'" x-cloak class="space-y-6">
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Social Links') }}</h3>
                    <p class="mb-4 text-xs text-luxury-muted">{{ __('Used in structured data and footer social links.') }}</p>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-admin.input-label for="facebook_url" value="{{ __('Facebook') }}" />
                            <x-admin.text-input id="facebook_url" name="facebook_url" type="url" value="{{ old('facebook_url', $settings->facebook_url) }}" placeholder="https://facebook.com/..." />
                            <x-admin.input-error :messages="$errors->get('facebook_url')" />
                        </div>
                        <div>
                            <x-admin.input-label for="instagram_url" value="{{ __('Instagram') }}" />
                            <x-admin.text-input id="instagram_url" name="instagram_url" type="url" value="{{ old('instagram_url', $settings->instagram_url) }}" placeholder="https://instagram.com/..." />
                            <x-admin.input-error :messages="$errors->get('instagram_url')" />
                        </div>
                        <div>
                            <x-admin.input-label for="twitter_url" value="{{ __('X / Twitter') }}" />
                            <x-admin.text-input id="twitter_url" name="twitter_url" type="url" value="{{ old('twitter_url', $settings->twitter_url) }}" placeholder="https://x.com/..." />
                            <x-admin.input-error :messages="$errors->get('twitter_url')" />
                        </div>
                        <div>
                            <x-admin.input-label for="linkedin_url" value="{{ __('LinkedIn') }}" />
                            <x-admin.text-input id="linkedin_url" name="linkedin_url" type="url" value="{{ old('linkedin_url', $settings->linkedin_url) }}" placeholder="https://linkedin.com/..." />
                            <x-admin.input-error :messages="$errors->get('linkedin_url')" />
                        </div>
                        <div>
                            <x-admin.input-label for="youtube_url" value="{{ __('YouTube') }}" />
                            <x-admin.text-input id="youtube_url" name="youtube_url" type="url" value="{{ old('youtube_url', $settings->youtube_url) }}" placeholder="https://youtube.com/..." />
                            <x-admin.input-error :messages="$errors->get('youtube_url')" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-admin.button type="submit" variant="primary">{{ __('Save Settings') }}</x-admin.button>
            </div>
        </form>
    </div>

    @push('styles')
        {{-- The "bare" build ships no visual theme at all (just layout/
             functional CSS) — every pixel below is ours, so there's nothing
             from Tom Select's own light-mode default theme left to fight
             with or override. --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.bare.min.css">
        <style>
            .ts-wrapper {
                width: 100%;
                position: relative;
            }

            /* The closed control — matches the admin text-input component exactly. */
            .ts-wrapper.single .ts-control {
                display: flex;
                align-items: center;
                width: 100%;
                min-height: 0;
                background-color: var(--color-luxury-charcoal);
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%238a8a92' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 0.9rem center;
                background-size: 1rem;
                border: 1px solid var(--color-luxury-border);
                border-radius: 0.5rem;
                padding: 0.75rem 2.5rem 0.75rem 1rem;
                font-size: 0.875rem;
                line-height: 1.25rem;
                color: var(--color-luxury-white);
                cursor: pointer;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }
            [dir="rtl"] .ts-wrapper.single .ts-control {
                background-position: left 0.9rem center;
                padding: 0.75rem 1rem 0.75rem 2.5rem;
            }
            .ts-wrapper.single .ts-control > .item {
                color: var(--color-luxury-white);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .ts-wrapper .ts-control input {
                color: var(--color-luxury-white) !important;
                font-size: 0.875rem !important;
                font-family: inherit !important;
            }
            .ts-wrapper .ts-control input::placeholder {
                color: var(--color-luxury-muted);
            }
            .ts-wrapper.focus .ts-control {
                border-color: var(--color-luxury-gold);
                box-shadow: 0 0 0 1px var(--color-luxury-gold);
            }

            /* Dropdown panel — matches the app's existing nav-dropdown look
               (rounded-2xl border bg-luxury-charcoal shadow-2xl/50). */
            .ts-dropdown {
                width: 100%;
                margin-top: 0.5rem;
                background-color: var(--color-luxury-charcoal);
                border: 1px solid var(--color-luxury-border);
                border-radius: 1rem;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                overflow: hidden;
                z-index: 60;
            }
            .ts-dropdown .ts-dropdown-input {
                background-color: var(--color-luxury-graphite);
                border: none;
                border-bottom: 1px solid var(--color-luxury-border);
                border-radius: 0;
                color: var(--color-luxury-white);
                font-size: 0.8125rem;
                padding: 0.75rem 1rem;
            }
            .ts-dropdown .ts-dropdown-input::placeholder {
                color: var(--color-luxury-muted);
            }
            .ts-dropdown .ts-dropdown-input:focus {
                outline: none;
                box-shadow: none;
            }
            .ts-dropdown .ts-dropdown-content {
                max-height: 15rem;
                padding: 0.375rem;
                scrollbar-width: thin;
                scrollbar-color: var(--color-luxury-slate) transparent;
            }
            .ts-dropdown .ts-dropdown-content::-webkit-scrollbar {
                width: 6px;
            }
            .ts-dropdown .ts-dropdown-content::-webkit-scrollbar-track {
                background: transparent;
            }
            .ts-dropdown .ts-dropdown-content::-webkit-scrollbar-thumb {
                background-color: var(--color-luxury-slate);
                border-radius: 9999px;
            }
            .ts-dropdown .option {
                color: var(--color-luxury-muted);
                border-radius: 0.625rem;
                padding: 0.625rem 0.75rem;
                font-size: 0.8125rem;
                cursor: pointer;
                transition: background-color 0.1s ease, color 0.1s ease;
            }
            .ts-dropdown .option.active {
                background-color: var(--color-luxury-graphite);
                color: var(--color-luxury-gold);
            }
            .ts-dropdown .option.selected {
                color: var(--color-luxury-gold);
                font-weight: 600;
            }
            .ts-dropdown .no-results {
                color: var(--color-luxury-muted);
                padding: 0.625rem 0.75rem;
                font-size: 0.8125rem;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                ['timezone', 'default_country'].forEach(function (id) {
                    const el = document.getElementById(id);
                    if (el) new TomSelect(el, { create: false, maxOptions: null });
                });
            });
        </script>
    @endpush
</x-admin.layouts.app>
