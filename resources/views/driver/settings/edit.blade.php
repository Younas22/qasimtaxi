<x-driver.layouts.app :title="__('Preferences')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Preferences') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Language and appearance — changes save automatically.') }}</p>
    </div>

    <div x-data="driverSettings({
            locale: @js($driver->locale ?? app()->getLocale()),
            theme: @js($driver->theme_mode ?? setting('theme_mode', 'dark')),
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
                        class="tap-scale flex items-center justify-center rounded-lg border px-3 py-2.5 text-sm transition">
                        <span class="truncate">{{ $language->native_name ?: $language->name }}</span>
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

        {{-- Browser Notifications --}}
        <x-push-notification-toggle :description="__('Get an instant alert the moment a new booking is assigned to you.')" />
    </div>

    <script>
        function driverSettings({ locale, theme }) {
            return {
                locale,
                theme,
                toast: '',
                toastTimer: null,

                async save(payload, successMessage) {
                    try {
                        const response = await fetch('{{ route('driver.settings.update') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(payload),
                        });

                        if (! response.ok) {
                            throw new Error('Request failed');
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

                setTheme(mode) {
                    this.theme = mode;
                    document.documentElement.classList.toggle('dark', mode !== 'light');
                    document.documentElement.setAttribute('data-theme', mode);
                    this.save({ theme_mode: mode }, @json(__('Appearance updated.')));
                },
            };
        }
    </script>
</x-driver.layouts.app>
