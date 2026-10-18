@props(['section'])

@php
    // Every contact detail — address/phone/email/whatsapp/hours/map embed —
    // comes from Settings > General, the single source of truth shared with
    // the footer, WhatsApp button, and invoices. This section is just a
    // placeholder marking where the block renders on the Contact page.
    $officePhone = setting('phone');
    $officeEmail = setting('email');
    $officeAddress = setting('address');
    $officeWhatsapp = setting('whatsapp');
    $officeMapEmbedUrl = setting('google_maps_embed_url');
    $businessHours = setting('business_hours_list');
    $whatsappDigits = $officeWhatsapp ? preg_replace('/\D+/', '', $officeWhatsapp) : null;
@endphp

<section class="border-b border-luxury-border">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($section->heading || $section->subheading)
            <div class="mx-auto max-w-2xl text-center">
                @if ($section->heading)
                    <h1 class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</h1>
                @endif
                @if ($section->subheading)
                    <p class="mt-3 text-luxury-muted">{{ __($section->subheading) }}</p>
                @endif
            </div>
        @endif

        <div class="mt-12 grid grid-cols-1 gap-8 lg:grid-cols-5">
            {{-- Contact form --}}
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 sm:p-8 lg:col-span-3">
                <h2 class="text-lg font-semibold text-luxury-white">{{ __('Send Us a Message') }}</h2>
                <p class="mt-1 text-sm text-luxury-muted">{{ __("We typically respond within a few hours.") }}</p>

                <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="contact-name" class="mb-1.5 block text-sm font-medium text-luxury-white">{{ __('Name') }}</label>
                            <input type="text" id="contact-name" name="name" value="{{ old('name') }}" required
                                class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            @error('name')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact-email" class="mb-1.5 block text-sm font-medium text-luxury-white">{{ __('Email') }}</label>
                            <input type="email" id="contact-email" name="email" value="{{ old('email') }}" required
                                class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            @error('email')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact-phone" class="mb-1.5 block text-sm font-medium text-luxury-white">{{ __('Phone') }} <span class="text-luxury-muted">({{ __('optional') }})</span></label>
                            <input type="tel" id="contact-phone" name="phone" value="{{ old('phone') }}"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact-subject" class="mb-1.5 block text-sm font-medium text-luxury-white">{{ __('Subject') }} <span class="text-luxury-muted">({{ __('optional') }})</span></label>
                            <input type="text" id="contact-subject" name="subject" value="{{ old('subject') }}"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            @error('subject')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="contact-message" class="mb-1.5 block text-sm font-medium text-luxury-white">{{ __('Message') }}</label>
                        <textarea id="contact-message" name="message" rows="5" required
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-luxury-gold px-6 py-3.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98] sm:w-auto">
                        <x-icon name="mail" class="h-4 w-4" />
                        {{ __('Send Message') }}
                    </button>
                </form>
            </div>

            {{-- Contact details + map --}}
            <div class="space-y-4 lg:col-span-2">
                @if ($officeAddress)
                    <div class="flex items-start gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="map-pin" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-sm font-medium text-luxury-white">{{ __('Office Address') }}</p>
                            <p class="mt-0.5 text-sm text-luxury-muted">{{ $officeAddress }}</p>
                        </div>
                    </div>
                @endif

                @if ($officePhone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $officePhone) }}" class="flex items-start gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="phone" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-sm font-medium text-luxury-white">{{ __('Phone') }}</p>
                            <p class="mt-0.5 text-sm text-luxury-muted">{{ $officePhone }}</p>
                        </div>
                    </a>
                @endif

                @if ($officeEmail)
                    <a href="mailto:{{ $officeEmail }}" class="flex items-start gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="mail" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-sm font-medium text-luxury-white">{{ __('Email') }}</p>
                            <p class="mt-0.5 text-sm text-luxury-muted">{{ $officeEmail }}</p>
                        </div>
                    </a>
                @endif

                @if ($whatsappDigits)
                    <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener" class="flex items-start gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="chat" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-sm font-medium text-luxury-white">{{ __('WhatsApp') }}</p>
                            <p class="mt-0.5 text-sm text-luxury-muted">{{ $officeWhatsapp }}</p>
                        </div>
                    </a>
                @endif

                @if (setting('business_hours'))
                    <div class="flex items-start gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="clock" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-luxury-white">{{ __('Working Hours') }}</p>
                            <dl class="mt-1.5 space-y-0.5">
                                @foreach ($businessHours as $day => $dayHours)
                                    <div class="flex items-center justify-between gap-3 text-sm text-luxury-muted">
                                        <dt>{{ __(ucfirst($day)) }}</dt>
                                        <dd>
                                            @if ($dayHours['closed'])
                                                {{ __('Closed') }}
                                            @elseif ($dayHours['open'] === $dayHours['close'])
                                                {{ __('Open 24 hours') }}
                                            @else
                                                {{ \Illuminate\Support\Carbon::parse($dayHours['open'])->format(app()->getLocale() === 'en' ? 'g:i A' : 'H:i') }}
                                                –
                                                {{ \Illuminate\Support\Carbon::parse($dayHours['close'])->format(app()->getLocale() === 'en' ? 'g:i A' : 'H:i') }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                @endif

                {{-- Google Maps --}}
                <div class="overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal">
                    @if ($officeMapEmbedUrl)
                        <iframe src="{{ $officeMapEmbedUrl }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            class="h-64 w-full border-0" allowfullscreen title="{{ __('Office location map') }}"></iframe>
                    @else
                        <div class="flex h-64 w-full flex-col items-center justify-center gap-2 bg-luxury-graphite px-4 text-center">
                            <x-icon name="map-pin" class="h-8 w-8 text-luxury-muted" />
                            <p class="text-sm font-medium text-luxury-white">{{ __('Map view coming soon') }}</p>
                            @if ($officeAddress)
                                <p class="max-w-xs text-xs text-luxury-muted">{{ $officeAddress }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
