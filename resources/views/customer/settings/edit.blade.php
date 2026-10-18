<x-customer.layouts.app :title="__('Preferences')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Preferences') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Language, currency, appearance, and notifications — changes save automatically.') }}</p>
    </div>

    <div x-data="userSettings({
            locale: @js($customer->locale ?? app()->getLocale()),
            currency: @js($customer->currency ?? active_currency()?->code),
            theme: @js($customer->theme_mode ?? setting('theme_mode', 'dark')),
            email: @js((bool) $customer->email_notifications_enabled),
        })" class="max-w-2xl space-y-6">

        {{-- Toast --}}
        <div x-show="toast" x-transition x-cloak
            class="fixed bottom-6 end-6 z-50 flex items-center gap-2 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black shadow-xl">
            <x-icon name="check-circle" class="h-4 w-4" />
            <span x-text="toast"></span>
        </div>

        {{-- Language --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Language') }}</h3>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($languages as $language)
                    <button type="button" @click="setLocale('{{ $language->code }}')"
                        :class="locale === '{{ $language->code }}' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40'"
                        class="tap-scale flex items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded-full bg-luxury-graphite">
                            @if ($language->flag_url)
                                <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <span class="truncate">{{ $language->native_name ?: $language->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Currency --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Currency') }}</h3>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($currencies as $currencyOption)
                    <button type="button" @click="setCurrency('{{ $currencyOption->code }}')"
                        :class="currency === '{{ $currencyOption->code }}' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40'"
                        class="tap-scale flex items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition">
                        <span class="w-5 shrink-0 text-center font-semibold">{{ $currencyOption->symbol }}</span>
                        <span class="truncate">{{ $currencyOption->code }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Appearance --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Appearance') }}</h3>
            <p class="mb-4 text-xs text-luxury-muted">{{ __('Applies to your dashboard only.') }}</p>
            <div class="grid grid-cols-2 gap-3 sm:max-w-sm">
                <button type="button" @click="setTheme('dark')"
                    :class="theme === 'dark' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40'"
                    class="tap-scale flex items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm transition">
                    <x-icon name="check-circle" class="h-4 w-4" x-show="theme === 'dark'" x-cloak />
                    {{ __('Dark') }}
                </button>
                <button type="button" @click="setTheme('light')"
                    :class="theme === 'light' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40'"
                    class="tap-scale flex items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm transition">
                    <x-icon name="check-circle" class="h-4 w-4" x-show="theme === 'light'" x-cloak />
                    {{ __('Light') }}
                </button>
            </div>
        </div>

        {{-- Notification Preferences --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Notification Preferences') }}</h3>

            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-luxury-border bg-luxury-graphite/40 p-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="mail" class="h-4 w-4" />
                        </span>
                        <p class="text-sm font-medium text-luxury-white">{{ __('Email Notifications') }}</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" x-model="email" @change="setEmail($event.target.checked)" class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-luxury-slate transition peer-checked:bg-luxury-gold"></span>
                        <span class="absolute start-1 top-1 h-4 w-4 rounded-full bg-luxury-white transition-transform peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                    </label>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-luxury-border bg-luxury-graphite/40 p-4 opacity-60">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-luxury-slate text-luxury-muted">
                            <x-icon name="phone" class="h-4 w-4" />
                        </span>
                        <p class="text-sm font-medium text-luxury-white">{{ __('SMS Notifications') }}</p>
                    </div>
                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-[11px] font-medium text-luxury-muted">{{ __('Soon') }}</span>
                </div>
            </div>
        </div>

        {{-- Browser Notifications — "Push Notifications" used to sit as a
             locked "Soon" row above, back when no provider was wired up.
             Real browser push now exists (see PushNotificationService), so
             it gets its own full enable/disable control here instead of a
             disabled placeholder toggle. --}}
        <x-push-notification-toggle :description="__('Get instant browser alerts about your bookings, right on this device.')" />
    </div>

    <script>
        function userSettings({ locale, currency, theme, email }) {
            return {
                locale,
                currency,
                theme,
                email,
                toast: '',
                toastTimer: null,

                async save(payload, successMessage) {
                    try {
                        const response = await fetch('{{ route('customer.settings.update') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(payload),
                        });

                        if (! response.ok) {
                            // Surface the actual validation message when the
                            // server sent one (422 with a Laravel-style
                            // {errors: {field: [messages]}} body), instead
                            // of always hiding it behind a generic toast.
                            let message = null;
                            if (response.status === 422) {
                                try {
                                    const body = await response.json();
                                    const firstField = body?.errors ? Object.keys(body.errors)[0] : null;
                                    message = firstField ? body.errors[firstField][0] : (body?.message ?? null);
                                } catch (parseError) {
                                    // Body wasn't JSON — fall through to the generic message below.
                                }
                            }

                            this.showToast(message || @json(__('Something went wrong. Please try again.')));
                            return;
                        }

                        this.showToast(successMessage);
                    } catch (e) {
                        this.showToast(@json(__('Something went wrong. Please try again.')));
                    }
                },

                showToast(message) {
                    this.toast = message;
                    clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => { this.toast = ''; }, 2500);
                },

                setLocale(code) {
                    this.locale = code;
                    this.save({ locale: code }, @json(__('Language updated.')));
                },

                setCurrency(code) {
                    this.currency = code;
                    this.save({ currency: code }, @json(__('Currency updated.')));
                },

                setTheme(mode) {
                    this.theme = mode;
                    document.documentElement.classList.toggle('dark', mode !== 'light');
                    document.documentElement.setAttribute('data-theme', mode);
                    this.save({ theme_mode: mode }, @json(__('Appearance updated.')));
                },

                setEmail(enabled) {
                    this.email = enabled;
                    this.save({ email_notifications_enabled: enabled }, @json(__('Notification preference updated.')));
                },
            };
        }
    </script>
</x-customer.layouts.app>
