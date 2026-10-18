@php
    $section = $section ?? null;
    $contact = $section?->contact_fields ?? [];
    $initialItems = old('items', $section?->content ?? []);
    $initialHeroButtons = old('hero_buttons', $section?->hero_buttons ?? []);

    if (($section?->type ?? old('type', 'hero')) === 'team' && ! empty($initialItems) && is_array($initialItems)) {
        $initialItems = collect($initialItems)->map(function ($item) {
            $existingPhoto = $item['existing_photo'] ?? ($item['photo'] ?? '');

            return [
                'existing_photo' => $existingPhoto,
                'photo_preview' => $existingPhoto ? asset('public/uploads/team/'.$existingPhoto) : '',
                'name' => $item['name'] ?? '',
                'role' => $item['role'] ?? '',
                'bio' => $item['bio'] ?? '',
            ];
        })->values()->all();
    }
@endphp

<div class="space-y-6"
    x-data="pageSectionForm({
        type: {{ \Illuminate\Support\Js::from(old('type', $section?->type ?? 'hero')) }},
        items: {{ \Illuminate\Support\Js::from(!empty($initialItems) && is_array($initialItems) && array_is_list($initialItems) ? $initialItems : []) }},
        heroButtons: {{ \Illuminate\Support\Js::from(!empty($initialHeroButtons) && is_array($initialHeroButtons) && array_is_list($initialHeroButtons) ? $initialHeroButtons : []) }},
    })">

    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <div>
            <x-admin.input-label for="type" value="{{ __('Section Type') }}" />
            <select id="type" name="type" x-model="type" required
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition sm:max-w-sm">
                @foreach (\App\Models\PageSection::TYPES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('type')" />
        </div>
    </div>

    {{-- Heading / Subheading (all types except faq subheading) --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Content') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="heading" value="{{ __('Heading') }}" />
                <x-admin.text-input id="heading" name="heading" type="text" value="{{ old('heading', $section?->heading) }}" />
                <x-admin.input-error :messages="$errors->get('heading')" />
            </div>

            <div x-show="type !== 'rich_text' && type !== 'faq'" x-cloak>
                <x-admin.input-label for="subheading" value="{{ __('Subheading') }}" />
                <x-admin.text-input id="subheading" name="subheading" type="text" value="{{ old('subheading', $section?->subheading) }}" />
                <x-admin.input-error :messages="$errors->get('subheading')" />
            </div>
        </div>

        {{-- Hero / CTA: eyebrow + differentiator --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2" x-show="type === 'hero' || type === 'cta'" x-cloak>
            <div>
                <x-admin.input-label for="eyebrow" value="{{ __('Eyebrow (optional)') }}" />
                <x-admin.text-input id="eyebrow" name="eyebrow" type="text" :placeholder="__('e.g. 🏷️ Start from $2, or Bilzen & surrounding area')" value="{{ old('eyebrow', $section?->eyebrow) }}" />
                <p class="mt-1 text-xs text-luxury-muted">{{ __('A short badge shown above the headline — a starting price, a location, or any short claim. Emoji work fine here.') }}</p>
                <x-admin.input-error :messages="$errors->get('eyebrow')" />
            </div>
            <div>
                <x-admin.input-label for="differentiator" value="{{ __('Differentiator (optional)') }}" />
                <x-admin.text-input id="differentiator" name="differentiator" type="text" x-bind:placeholder="type === 'cta' ? '{{ __('e.g. 24/7 Support · Secure Booking · Professional Chauffeurs') }}' : '{{ __('e.g. Fixed prices. No hidden fees.') }}'" value="{{ old('differentiator', $section?->differentiator) }}" />
                <p class="mt-1 text-xs text-luxury-muted" x-show="type === 'hero'">{{ __('One specific, true claim shown as a highlighted line near the booking widget — only use something you can actually stand behind.') }}</p>
                <p class="mt-1 text-xs text-luxury-muted" x-show="type === 'cta'" x-cloak>{{ __('A small, understated trust line shown below the button.') }}</p>
                <x-admin.input-error :messages="$errors->get('differentiator')" />
            </div>
        </div>

        {{-- Rich text body --}}
        <div x-show="type === 'rich_text'" x-cloak>
            <x-admin.input-label value="{{ __('Body') }}" />
            <x-admin.richtext-editor name="body" :value="old('body', $section?->body)" />
            <x-admin.input-error :messages="$errors->get('body')" />
        </div>

        {{-- Image (hero / cta) --}}
        <div x-show="type === 'hero' || type === 'cta'" x-cloak>
            <x-admin.input-label for="image" value="{{ __('Image') }}" />
            @if ($section?->image_url)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ $section->image_url }}" alt="" class="h-16 w-28 rounded-lg border border-luxury-border object-cover">
                    <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                        <input type="checkbox" name="remove_image" value="1" class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-red-400 focus:ring-1 focus:ring-red-400">
                        {{ __('Remove current image') }}
                    </label>
                </div>
            @endif
            <input id="image" name="image" type="file" accept="image/*"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-2.5 text-sm text-luxury-white file:mr-3 file:rounded-md file:border-0 file:bg-luxury-slate file:px-3 file:py-1.5 file:text-xs file:text-luxury-white">
            <x-admin.input-error :messages="$errors->get('image')" />
            <p class="mt-1 text-xs text-luxury-muted" x-show="type === 'hero'">{{ __('Used as the background poster, and as the fallback if no video is uploaded below.') }}</p>
            <p class="mt-1 text-xs text-luxury-muted" x-show="type === 'cta'" x-cloak>{{ __('Optional. Adds a large visual (e.g. a car photo) alongside the text. Left blank, this section shows as a simple centered banner instead.') }}</p>
        </div>

        {{-- Video (hero only, full-bleed background) --}}
        <div x-show="type === 'hero'" x-cloak>
            <x-admin.input-label for="video" value="{{ __('Background Video (optional)') }}" />
            @if ($section?->video_url)
                <div class="mb-2 flex items-center gap-3">
                    <video src="{{ $section->video_url }}" class="h-16 w-28 rounded-lg border border-luxury-border object-cover" muted></video>
                    <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                        <input type="checkbox" name="remove_video" value="1" class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-red-400 focus:ring-1 focus:ring-red-400">
                        {{ __('Remove current video') }}
                    </label>
                </div>
            @endif
            <input id="video" name="video" type="file" accept="video/mp4,video/webm,video/quicktime"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-2.5 text-sm text-luxury-white file:mr-3 file:rounded-md file:border-0 file:bg-luxury-slate file:px-3 file:py-1.5 file:text-xs file:text-luxury-white">
            <x-admin.input-error :messages="$errors->get('video')" />
            <p class="mt-1 text-xs text-luxury-muted">{{ __('MP4 or WebM, up to 25MB. Takes priority over the image above when present.') }}</p>
        </div>

        {{-- Primary button (cta only — hero uses the Hero Action Buttons repeater below) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2" x-show="type === 'cta'" x-cloak>
            <div>
                <x-admin.input-label for="button_text" value="{{ __('Primary Button Text') }}" />
                <x-admin.text-input id="button_text" name="button_text" type="text" value="{{ old('button_text', $section?->button_text) }}" />
                <x-admin.input-error :messages="$errors->get('button_text')" />
            </div>
            <div>
                <x-admin.input-label for="button_url" value="{{ __('Primary Button URL') }}" />
                <x-admin.text-input id="button_url" name="button_url" type="text" placeholder="/contact" value="{{ old('button_url', $section?->button_url) }}" />
                <x-admin.input-error :messages="$errors->get('button_url')" />
            </div>
        </div>

        {{-- Secondary button (cta only) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2" x-show="type === 'cta'" x-cloak>
            <div>
                <x-admin.input-label for="button_text_2" value="{{ __('Secondary Button Text (optional)') }}" />
                <x-admin.text-input id="button_text_2" name="button_text_2" type="text" value="{{ old('button_text_2', $section?->button_text_2) }}" />
                <x-admin.input-error :messages="$errors->get('button_text_2')" />
            </div>
            <div>
                <x-admin.input-label for="button_url_2" value="{{ __('Secondary Button URL') }}" />
                <x-admin.text-input id="button_url_2" name="button_url_2" type="text" placeholder="/about" value="{{ old('button_url_2', $section?->button_url_2) }}" />
                <x-admin.input-error :messages="$errors->get('button_url_2')" />
            </div>
        </div>

        {{-- Action Buttons (hero only) — this one list drives BOTH this hero
             section and, for the home page specifically, the site's top
             navbar (see components/header.blade.php). --}}
        <div x-show="type === 'hero'" x-cloak class="space-y-4 border-t border-luxury-border pt-5">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-luxury-white">{{ __('Action Buttons') }}</h4>
                    <p class="mt-1 text-xs text-luxury-muted">
                        @if ($page->slug === 'home')
                            {{ __("Book Now, Call Now, your phone number, the Pricing modal, or any custom link — managed here for both this hero AND the site's top navbar. Each button gets its own Desktop/Mobile toggle for each location.") }}
                        @else
                            {{ __('Book Now, Call Now, your phone number, the Pricing modal, or any custom link — choose which show, and whether each shows on desktop, mobile, or both. (Only the home page\'s hero also feeds the top navbar.)') }}
                        @endif
                    </p>
                </div>
                <button type="button" @click="heroButtons.push({ id: '', kind: 'custom', label: '', url: '', hero_show_desktop: true, hero_show_mobile: true, navbar_show_desktop: true, navbar_show_mobile: true, bg_color: '', text_color: '' })" class="shrink-0 text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                    {{ __('+ Add Button') }}
                </button>
            </div>

            <template x-for="(button, index) in heroButtons" :key="index">
                <div class="space-y-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                    <input type="hidden" :name="'hero_buttons[' + index + '][id]'" x-model="button.id">

                    <div class="flex items-start justify-between gap-3">
                        <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Type') }}</label>
                                <select :name="'hero_buttons[' + index + '][kind]'" x-model="button.kind"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                    <option value="book_now">{{ __('Book Now (link)') }}</option>
                                    <option value="call_now">{{ __('Call Now') }}</option>
                                    <option value="phone_number">{{ __('Show Phone Number') }}</option>
                                    <option value="price">{{ __('Pricing (opens the pricing chart)') }}</option>
                                    <option value="custom">{{ __('Custom Link') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Label (optional)') }}</label>
                                <input type="text" :name="'hero_buttons[' + index + '][label]'" x-model="button.label"
                                    :placeholder="{ book_now: '{{ __('Book Now') }}', call_now: '{{ __('Call Now') }}', phone_number: '{{ setting('phone') ?: __('shows the number as-is') }}', price: '{{ __('Pricing') }}', custom: '{{ __('Learn More') }}' }[button.kind]"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div class="sm:col-span-2" x-show="button.kind === 'book_now' || button.kind === 'custom'">
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('URL') }}</label>
                                <input type="text" :name="'hero_buttons[' + index + '][url]'" x-model="button.url" placeholder="/#booking-widget"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <p class="text-xs text-luxury-muted sm:col-span-2" x-show="button.kind === 'call_now' || button.kind === 'phone_number'">
                                {{ __('Uses the phone number from') }}
                                <a href="{{ route('admin.settings.edit') }}" class="text-luxury-gold hover:text-luxury-gold-light">{{ __('Settings → Contact & Hours') }}</a>.
                                {{ __("Won't show if no phone number is set there.") }}
                            </p>

                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Background Color') }}</label>
                                <div class="flex items-center gap-2">
                                    <label class="relative h-9 w-9 shrink-0 cursor-pointer overflow-hidden rounded-lg border border-luxury-border">
                                        <input type="color" x-model="button.bg_color"
                                            class="absolute -inset-2 h-[calc(100%+1rem)] w-[calc(100%+1rem)] cursor-pointer border-0 p-0">
                                    </label>
                                    <input type="text" :name="'hero_buttons[' + index + '][bg_color]'" x-model="button.bg_color" placeholder="{{ __('Default') }}" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7"
                                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 font-mono text-xs uppercase text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                    <button type="button" x-show="button.bg_color" x-cloak @click="button.bg_color = ''" class="shrink-0 text-xs text-luxury-muted hover:text-luxury-white">{{ __('Clear') }}</button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Text Color') }}</label>
                                <div class="flex items-center gap-2">
                                    <label class="relative h-9 w-9 shrink-0 cursor-pointer overflow-hidden rounded-lg border border-luxury-border">
                                        <input type="color" x-model="button.text_color"
                                            class="absolute -inset-2 h-[calc(100%+1rem)] w-[calc(100%+1rem)] cursor-pointer border-0 p-0">
                                    </label>
                                    <input type="text" :name="'hero_buttons[' + index + '][text_color]'" x-model="button.text_color" placeholder="{{ __('Default') }}" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7"
                                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 font-mono text-xs uppercase text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                    <button type="button" x-show="button.text_color" x-cloak @click="button.text_color = ''" class="shrink-0 text-xs text-luxury-muted hover:text-luxury-white">{{ __('Clear') }}</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="heroButtons.splice(index, 1)" class="shrink-0 text-xs text-red-400 hover:text-red-300">{{ __('Remove') }}</button>
                    </div>

                    <div class="grid grid-cols-1 gap-3 border-t border-luxury-border/60 pt-3 {{ $page->slug === 'home' ? 'sm:grid-cols-2' : '' }}">
                        <div>
                            <p class="mb-1.5 text-xs font-medium uppercase tracking-wide text-luxury-muted">{{ __('In the Hero') }}</p>
                            <div class="flex flex-wrap gap-x-5 gap-y-1.5">
                                <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                                    <input type="checkbox" :name="'hero_buttons[' + index + '][hero_show_desktop]'" value="1" x-model="button.hero_show_desktop"
                                        class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                                    {{ __('Show on Desktop') }}
                                </label>
                                <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                                    <input type="checkbox" :name="'hero_buttons[' + index + '][hero_show_mobile]'" value="1" x-model="button.hero_show_mobile"
                                        class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                                    {{ __('Show on Mobile') }}
                                </label>
                            </div>
                        </div>
                        @if ($page->slug === 'home')
                            <div>
                                <p class="mb-1.5 text-xs font-medium uppercase tracking-wide text-luxury-muted">{{ __('In the Top Navbar') }}</p>
                                <div class="flex flex-wrap gap-x-5 gap-y-1.5">
                                    <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                                        <input type="checkbox" :name="'hero_buttons[' + index + '][navbar_show_desktop]'" value="1" x-model="button.navbar_show_desktop"
                                            class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                                        {{ __('Show on Desktop') }}
                                    </label>
                                    <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                                        <input type="checkbox" :name="'hero_buttons[' + index + '][navbar_show_mobile]'" value="1" x-model="button.navbar_show_mobile"
                                            class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                                        {{ __('Show on Mobile') }}
                                    </label>
                                </div>
                            </div>
                        @else
                            {{-- Not the home page — its hero doesn't feed the navbar, but the
                                 fields still need to round-trip so a later home-page edit
                                 doesn't see them reset. --}}
                            <input type="hidden" :name="'hero_buttons[' + index + '][navbar_show_desktop]'" :value="button.navbar_show_desktop ? '1' : '0'">
                            <input type="hidden" :name="'hero_buttons[' + index + '][navbar_show_mobile]'" :value="button.navbar_show_mobile ? '1' : '0'">
                        @endif
                    </div>
                </div>
            </template>
            <p class="text-xs text-luxury-muted" x-show="heroButtons.length === 0" x-cloak>{{ __('No buttons — the hero shows no action buttons at all.') }}</p>
        </div>
    </div>

    {{-- Areas note --}}
    <div class="space-y-3 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'areas'" x-cloak>
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Service Areas') }}</h3>
        <p class="text-xs text-luxury-muted">
            {{ __('The list of towns/cities is managed at') }}
            <a href="{{ route('admin.areas.index') }}" class="text-luxury-gold hover:text-luxury-gold-light">{{ __('Service Areas') }}</a>.
            {{ __('This section just marks where that list appears on this page — use the Heading/Subheading fields above if you want a title above it.') }}
        </p>
    </div>

    {{-- Promotions note --}}
    <div class="space-y-3 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'promotions'" x-cloak>
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Promotions') }}</h3>
        <p class="text-xs text-luxury-muted">
            {{ __('The promotions themselves (image, title, dates) are managed at') }}
            <a href="{{ route('admin.promotions.index') }}" class="text-luxury-gold hover:text-luxury-gold-light">{{ __('Promotions') }}</a>.
            {{ __('This section just marks where currently-active ones appear on this page — use the Heading/Subheading fields above if you want a title above it. Nothing shows here when no promotion is currently active.') }}
        </p>
    </div>

    {{-- Contact info fields --}}
    <div class="space-y-3 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'contact_info'" x-cloak>
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Contact Details') }}</h3>
        <p class="text-xs text-luxury-muted">
            {{ __('Address, phone, email, WhatsApp, business hours, and the map embed are all managed in one place —') }}
            <a href="{{ route('admin.settings.edit') }}" class="text-luxury-gold hover:text-luxury-gold-light">{{ __('Settings → General') }}</a>.
            {{ __('This section just marks where that block appears on the Contact page — use the Heading/Subheading fields above if you want a title above it.') }}
        </p>
    </div>

    {{-- Vision & Mission fields --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'vision_mission'" x-cloak>
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Vision & Mission') }}</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="space-y-4 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-luxury-gold">{{ __('Vision') }}</p>
                <div>
                    <x-admin.input-label for="vision_icon" value="{{ __('Icon') }}" />
                    <select id="vision_icon" name="vision_icon"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        @foreach (array_keys(config('icons')) as $iconName)
                            <option value="{{ $iconName }}" @selected(old('vision_icon', $section?->vision_mission['vision_icon'] ?? 'eye') === $iconName)>{{ ucfirst(str_replace('-', ' ', $iconName)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-admin.input-label for="vision_title" value="{{ __('Title') }}" />
                    <x-admin.text-input id="vision_title" name="vision_title" type="text" value="{{ old('vision_title', $section?->vision_mission['vision_title'] ?? __('Our Vision')) }}" />
                </div>
                <div>
                    <x-admin.input-label for="vision_body" value="{{ __('Body') }}" />
                    <textarea id="vision_body" name="vision_body" rows="4"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('vision_body', $section?->vision_mission['vision_body'] ?? '') }}</textarea>
                    <x-admin.input-error :messages="$errors->get('vision_body')" />
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-luxury-gold">{{ __('Mission') }}</p>
                <div>
                    <x-admin.input-label for="mission_icon" value="{{ __('Icon') }}" />
                    <select id="mission_icon" name="mission_icon"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        @foreach (array_keys(config('icons')) as $iconName)
                            <option value="{{ $iconName }}" @selected(old('mission_icon', $section?->vision_mission['mission_icon'] ?? 'trending-up') === $iconName)>{{ ucfirst(str_replace('-', ' ', $iconName)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-admin.input-label for="mission_title" value="{{ __('Title') }}" />
                    <x-admin.text-input id="mission_title" name="mission_title" type="text" value="{{ old('mission_title', $section?->vision_mission['mission_title'] ?? __('Our Mission')) }}" />
                </div>
                <div>
                    <x-admin.input-label for="mission_body" value="{{ __('Body') }}" />
                    <textarea id="mission_body" name="mission_body" rows="4"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('mission_body', $section?->vision_mission['mission_body'] ?? '') }}</textarea>
                    <x-admin.input-error :messages="$errors->get('mission_body')" />
                </div>
            </div>
        </div>
    </div>

    {{-- Testimonial settings --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'testimonials'" x-cloak>
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Testimonial Settings') }}</h3>
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Pulls live from approved customer reviews — no manual content to enter here.') }}</p>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="testimonial_limit" value="{{ __('Number to Show') }}" />
                <x-admin.text-input id="testimonial_limit" name="testimonial_limit" type="number" min="1" max="24"
                    value="{{ old('testimonial_limit', $section?->testimonial_settings['limit'] ?? 6) }}" />
                <x-admin.input-error :messages="$errors->get('testimonial_limit')" />
            </div>
            <div>
                <x-admin.input-label for="testimonial_min_rating" value="{{ __('Minimum Rating') }}" />
                <select id="testimonial_min_rating" name="testimonial_min_rating"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    @php $minRating = old('testimonial_min_rating', $section?->testimonial_settings['min_rating'] ?? 4); @endphp
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected((string) $minRating === (string) $i)>{{ trans_choice(':count Star & Up|:count Stars & Up', $i, ['count' => $i]) }}</option>
                    @endfor
                </select>
                <x-admin.input-error :messages="$errors->get('testimonial_min_rating')" />
            </div>
        </div>
    </div>

    {{-- Fleet settings --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'fleet'" x-cloak>
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Fleet Settings') }}</h3>
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Pulls live from your active vehicles and categories — no manual content to enter here.') }}</p>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="fleet_limit" value="{{ __('Maximum Vehicles to Show') }}" />
                <x-admin.text-input id="fleet_limit" name="fleet_limit" type="number" min="1" max="48"
                    value="{{ old('fleet_limit', $section?->fleet_settings['limit'] ?? 12) }}" />
                <x-admin.input-error :messages="$errors->get('fleet_limit')" />
            </div>
            <div>
                <x-admin.input-label for="fleet_category_id" value="{{ __('Featured Category (optional)') }}" />
                <select id="fleet_category_id" name="fleet_category_id"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach (\App\Models\VehicleCategory::active()->ordered()->get() as $category)
                        <option value="{{ $category->id }}" @selected((string) old('fleet_category_id', $section?->fleet_settings['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-luxury-muted">{{ __('Limit this section to a single fleet category, e.g. "Luxury" for a VIP page.') }}</p>
                <x-admin.input-error :messages="$errors->get('fleet_category_id')" />
            </div>
        </div>
    </div>

    {{-- Popular routes settings --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'routes'" x-cloak>
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Popular Routes Settings') }}</h3>
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Pulls live from your Popular Routes list (Admin → Popular Routes) — no manual content to enter here.') }}</p>
        </div>
        <div>
            <x-admin.input-label for="route_limit" value="{{ __('Maximum Routes per Group') }}" />
            <x-admin.text-input id="route_limit" name="route_limit" type="number" min="1" max="24" class="sm:max-w-xs"
                value="{{ old('route_limit', $section?->route_settings['limit'] ?? 6) }}" />
            <x-admin.input-error :messages="$errors->get('route_limit')" />
        </div>
    </div>

    {{-- Blog settings --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'blog'" x-cloak>
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Blog Settings') }}</h3>
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Pulls live from your published blog posts (Admin → Blog) — no manual content to enter here.') }}</p>
        </div>
        <div>
            <x-admin.input-label for="blog_limit" value="{{ __('Maximum Latest Posts to Show') }}" />
            <x-admin.text-input id="blog_limit" name="blog_limit" type="number" min="1" max="24" class="sm:max-w-xs"
                value="{{ old('blog_limit', $section?->blog_settings['limit'] ?? 6) }}" />
            <x-admin.input-error :messages="$errors->get('blog_limit')" />
        </div>
    </div>

    {{-- Repeatable items (items / trust_badges / faq / stats / team / process) --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'items' || type === 'trust_badges' || type === 'faq' || type === 'stats' || type === 'team' || type === 'process'" x-cloak>
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-luxury-white" x-text="type === 'faq' ? 'Questions' : (type === 'stats' ? 'Stats' : (type === 'team' ? 'Team Members' : (type === 'process' ? 'Steps' : (type === 'trust_badges' ? 'Trust Badges' : 'Items'))))"></h3>
            <button type="button" @click="items.push({ icon: 'star', title: '', description: '', link: '', question: '', answer: '', category: '', label: '', value: '', suffix: '', name: '', role: '', bio: '', existing_photo: '', photo_preview: '' })" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                {{ __('+ Add') }} <span x-text="type === 'faq' ? 'Question' : (type === 'stats' ? 'Stat' : (type === 'team' ? 'Team Member' : (type === 'process' ? 'Step' : (type === 'trust_badges' ? 'Badge' : 'Item'))))"></span>
            </button>
        </div>

        <p class="text-xs text-luxury-muted" x-show="type === 'trust_badges'" x-cloak>
            {{ __('Only add claims that are actually true for this business — never an invented statistic or an unverified claim (e.g. don\'t add "Licensed & Insured" unless it really is).') }}
        </p>

        <template x-for="(item, index) in items" :key="index">
            <div class="space-y-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wide text-luxury-muted" x-text="type === 'faq' ? 'Question ' + (index + 1) : (type === 'stats' ? 'Stat ' + (index + 1) : (type === 'team' ? 'Team Member ' + (index + 1) : (type === 'process' ? 'Step ' + (index + 1) : (type === 'trust_badges' ? 'Badge ' + (index + 1) : 'Item ' + (index + 1)))))"></span>
                    <button type="button" @click="items.splice(index, 1)" class="text-xs text-red-400 hover:text-red-300">{{ __('Remove') }}</button>
                </div>

                {{-- items / trust_badges / process fields (process has no link) --}}
                <template x-if="type === 'items' || type === 'trust_badges' || type === 'process'">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">{{ __('Icon') }}</label>
                            <select :name="'items[' + index + '][icon]'" x-model="item.icon"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                @foreach (array_keys(config('icons')) as $iconName)
                                    <option value="{{ $iconName }}">{{ ucfirst(str_replace('-', ' ', $iconName)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted" x-text="type === 'trust_badges' ? '{{ __('Title (e.g. Licensed & Insured)') }}' : '{{ __('Title') }}'"></label>
                            <input type="text" :name="'items[' + index + '][title]'" x-model="item.title"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        </div>
                        <div class="sm:col-span-2" x-show="type !== 'trust_badges'">
                            <label class="mb-1 block text-xs text-luxury-muted">{{ __('Description') }}</label>
                            <textarea :name="'items[' + index + '][description]'" x-model="item.description" rows="2"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold"></textarea>
                        </div>
                        <div class="sm:col-span-2" x-show="type === 'trust_badges'">
                            <label class="mb-1 block text-xs text-luxury-muted">{{ __('Short note (optional)') }}</label>
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" placeholder="{{ __('e.g. Registered with the local transport authority') }}"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        </div>
                        <div class="sm:col-span-2" x-show="type === 'items' || type === 'trust_badges'">
                            <label class="mb-1 block text-xs text-luxury-muted">{{ __('Link (optional)') }}</label>
                            <input type="text" :name="'items[' + index + '][link]'" x-model="item.link" placeholder="/services"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        </div>
                    </div>
                </template>

                {{-- faq fields --}}
                <template x-if="type === 'faq'">
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Question') }}</label>
                                <input type="text" :name="'items[' + index + '][question]'" x-model="item.question"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Category (optional)') }}</label>
                                <input type="text" :name="'items[' + index + '][category]'" x-model="item.category" placeholder="{{ __('e.g. Booking, Payments') }}"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">{{ __('Answer') }}</label>
                            <textarea :name="'items[' + index + '][answer]'" x-model="item.answer" rows="2"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold"></textarea>
                        </div>
                    </div>
                </template>

                {{-- stats fields --}}
                <template x-if="type === 'stats'">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">{{ __('Icon') }}</label>
                            <select :name="'items[' + index + '][icon]'" x-model="item.icon"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                @foreach (array_keys(config('icons')) as $iconName)
                                    <option value="{{ $iconName }}">{{ ucfirst(str_replace('-', ' ', $iconName)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">{{ __('Label') }}</label>
                            <input type="text" :name="'items[' + index + '][label]'" x-model="item.label" placeholder="{{ __('e.g. Total Rides') }}"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">{{ __('Value') }}</label>
                            <div class="flex gap-2">
                                <input type="number" min="0" :name="'items[' + index + '][value]'" x-model="item.value" placeholder="e.g. 50000"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                <input type="text" :name="'items[' + index + '][suffix]'" x-model="item.suffix" placeholder="+" maxlength="10"
                                    class="w-16 shrink-0 rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                        </div>
                    </div>
                </template>

                {{-- team fields --}}
                <template x-if="type === 'team'">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[5rem_1fr]">
                        <div class="flex flex-col items-center gap-2">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                                <template x-if="item.photo_preview">
                                    <img :src="item.photo_preview" alt="" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!item.photo_preview">
                                    <span class="text-[10px] text-luxury-muted">{{ __('No photo') }}</span>
                                </template>
                            </div>
                            <label class="cursor-pointer text-[11px] text-luxury-gold hover:text-luxury-gold-light">
                                <span>{{ __('Upload') }}</span>
                                <input type="file" :name="'items[' + index + '][photo]'" accept="image/*" class="hidden"
                                    @change="item.photo_preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : item.photo_preview">
                            </label>
                            <input type="hidden" :name="'items[' + index + '][existing_photo]'" x-model="item.existing_photo">
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Name') }}</label>
                                <input type="text" :name="'items[' + index + '][name]'" x-model="item.name"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Role') }}</label>
                                <input type="text" :name="'items[' + index + '][role]'" x-model="item.role" placeholder="{{ __('e.g. Chief Executive Officer') }}"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs text-luxury-muted">{{ __('Bio (optional)') }}</label>
                                <textarea :name="'items[' + index + '][bio]'" x-model="item.bio" rows="2"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
        <x-admin.input-error :messages="$errors->get('items')" />
    </div>
</div>

<script>
    function pageSectionForm(config) {
        return {
            type: config.type,
            items: config.items.length ? config.items.map((item) => ({
                icon: item.icon || 'star',
                title: item.title || '',
                description: item.description || '',
                link: item.link || '',
                question: item.question || '',
                answer: item.answer || '',
                category: item.category || '',
                label: item.label || '',
                value: item.value ?? '',
                suffix: item.suffix || '',
                name: item.name || '',
                role: item.role || '',
                bio: item.bio || '',
                existing_photo: item.existing_photo || '',
                photo_preview: item.photo_preview || '',
            })) : [],
            heroButtons: config.heroButtons.length ? config.heroButtons.map((button) => ({
                id: button.id || '',
                kind: button.kind || 'custom',
                label: button.label || '',
                url: button.url || '',
                // A checkbox absent from a stale old()-repopulated request
                // (i.e. the admin had just unchecked it) means false, not
                // "unset" — so this only defaults to true for a value that
                // was never present at all (undefined), not one already
                // present-but-falsy.
                hero_show_desktop: button.hero_show_desktop === undefined ? true : (button.hero_show_desktop === true || button.hero_show_desktop === '1' || button.hero_show_desktop === 1),
                hero_show_mobile: button.hero_show_mobile === undefined ? true : (button.hero_show_mobile === true || button.hero_show_mobile === '1' || button.hero_show_mobile === 1),
                navbar_show_desktop: button.navbar_show_desktop === undefined ? true : (button.navbar_show_desktop === true || button.navbar_show_desktop === '1' || button.navbar_show_desktop === 1),
                navbar_show_mobile: button.navbar_show_mobile === undefined ? true : (button.navbar_show_mobile === true || button.navbar_show_mobile === '1' || button.navbar_show_mobile === 1),
                bg_color: button.bg_color || '',
                text_color: button.text_color || '',
            })) : [],
        };
    }
</script>
