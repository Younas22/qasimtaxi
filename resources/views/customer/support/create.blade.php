<x-customer.layouts.app :title="__('New Support Ticket')">
    <div class="mb-6">
        <a href="{{ route('customer.support.index') }}" class="mb-2 inline-flex items-center gap-1.5 text-sm text-luxury-muted hover:text-luxury-white">
            <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180" />
            {{ __('Back to Support') }}
        </a>
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('New Support Ticket') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __("Tell us what's going on and we'll get back to you shortly.") }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 lg:col-span-2">
            <form method="POST" action="{{ route('customer.support.store') }}" class="space-y-5">
                @csrf

                <div>
                    <x-admin.input-label for="subject" value="Subject" />
                    <x-admin.text-input id="subject" name="subject" type="text" value="{{ old('subject') }}" required autofocus />
                    <x-admin.input-error :messages="$errors->get('subject')" />
                </div>

                @if ($bookings->isNotEmpty())
                    <div>
                        <x-admin.input-label for="booking_id" value="Related Booking (optional)" />
                        <select id="booking_id" name="booking_id"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}" @selected((string) old('booking_id') === (string) $booking->id)>
                                    {{ $booking->booking_number }} — {{ $booking->pickup_datetime->format('M d, Y') }}
                                </option>
                            @endforeach
                        </select>
                        <x-admin.input-error :messages="$errors->get('booking_id')" />
                    </div>
                @endif

                <div>
                    <x-admin.input-label for="message" value="Message" />
                    <textarea id="message" name="message" rows="6" required
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('message') }}</textarea>
                    <x-admin.input-error :messages="$errors->get('message')" />
                </div>

                <x-admin.button type="submit" variant="primary">{{ __('Submit Ticket') }}</x-admin.button>
            </form>
        </div>

        <div class="space-y-4">
            @if (setting('phone'))
                <a href="tel:{{ setting('phone') }}" class="flex items-start gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                        <x-icon name="phone" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-sm font-medium text-luxury-white">{{ __('Call Us') }}</p>
                        <p class="mt-0.5 text-sm text-luxury-muted">{{ setting('phone') }}</p>
                    </div>
                </a>
            @endif

            @if (setting('whatsapp'))
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', setting('whatsapp')) }}" target="_blank" rel="noopener"
                    class="flex items-start gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                        <x-icon name="chat" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-sm font-medium text-luxury-white">{{ __('WhatsApp') }}</p>
                        <p class="mt-0.5 text-sm text-luxury-muted">{{ setting('whatsapp') }}</p>
                    </div>
                </a>
            @endif

            @if (setting('email'))
                <a href="mailto:{{ setting('email') }}" class="flex items-start gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                        <x-icon name="mail" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-sm font-medium text-luxury-white">{{ __('Email') }}</p>
                        <p class="mt-0.5 text-sm text-luxury-muted">{{ setting('email') }}</p>
                    </div>
                </a>
            @endif

            <div class="rounded-xl border border-luxury-border bg-luxury-charcoal p-4 opacity-60">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-luxury-slate text-luxury-muted">
                            <x-icon name="chat" class="h-4 w-4" />
                        </span>
                        <p class="text-sm font-medium text-luxury-white">{{ __('Live Chat') }}</p>
                    </div>
                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-[11px] font-medium text-luxury-muted">{{ __('Soon') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-customer.layouts.app>
