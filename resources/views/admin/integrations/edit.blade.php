<x-admin.layouts.app :title="__('Integrations')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Integrations') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Third-party API keys used across the site.') }}</p>
    </div>

    <div class="max-w-2xl space-y-6" x-data="googleMapsKeyForm(@js($googleMapsKeyMasked), '{{ route('admin.integrations.google-maps.test') }}')">
        <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Google Maps API Key') }}</h3>
                @if ($googleMapsKeySet)
                    <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-medium text-emerald-400">{{ __('Configured') }}</span>
                @else
                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-[11px] font-medium text-luxury-muted">{{ __('Not set') }}</span>
                @endif
            </div>
            <p class="text-xs text-luxury-muted">
                {{ __('GOOGLE_MAPS_API_KEY — used for Places Autocomplete, Distance Matrix, Geocoding, and the live dispatch map. Saving here writes directly to the .env file on the server.') }}
            </p>
            <p class="text-xs text-luxury-muted">
                {{ __('Don\'t have a key yet?') }}
                <a href="https://console.cloud.google.com/google/maps-apis/credentials" target="_blank" rel="noopener noreferrer" class="text-luxury-gold underline hover:no-underline">
                    {{ __('Get an API key from Google Cloud Console') }}
                </a>
            </p>

            <form @submit.prevent="save('{{ route('admin.integrations.google-maps.update') }}')">
                <x-admin.input-label for="google_maps_api_key" value="{{ __('API Key') }}" />
                <div class="flex items-center gap-2">
                    <input :type="reveal ? 'text' : 'password'" id="google_maps_api_key" name="google_maps_api_key"
                        x-model="key" autocomplete="off" :disabled="saving"
                        placeholder="{{ $googleMapsKeyMasked ?: 'AIza...' }}"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition disabled:opacity-60">
                    <button type="button" @click="reveal = !reveal" class="shrink-0 rounded-lg border border-luxury-border px-3 py-3 text-xs text-luxury-muted hover:text-luxury-gold">
                        <span x-text="reveal ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
                    </button>
                </div>
                <p class="mt-1 text-xs text-luxury-muted">{{ __('Leave blank to keep the currently saved key — only used to test or save a new one.') }}</p>
                @error('google_maps_api_key')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror

                <template x-if="testResult">
                    <p class="mt-3 text-xs" :class="testResult.ok ? 'text-emerald-400' : 'text-red-400'" x-text="testResult.message"></p>
                </template>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <x-admin.button type="button" variant="secondary" @click="test()" x-bind:disabled="testing">
                        <span x-show="!testing">{{ __('Test Connection') }}</span>
                        <span x-show="testing" x-cloak>{{ __('Testing…') }}</span>
                    </x-admin.button>
                    <x-admin.button type="submit" variant="primary">
                        <span x-show="!saving">{{ __('Save to .env') }}</span>
                        <span x-show="saving" x-cloak>{{ __('Saving…') }}</span>
                    </x-admin.button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function googleMapsKeyForm(maskedKey, testUrl) {
                return {
                    key: '',
                    reveal: false,
                    testing: false,
                    saving: false,
                    testResult: null,

                    async test() {
                        const value = this.key || maskedKey;
                        if (! value) {
                            this.testResult = { ok: false, message: @js(__('Enter an API key first.')) };
                            return;
                        }

                        this.testing = true;
                        this.testResult = null;

                        try {
                            const response = await fetch(testUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ key: this.key || maskedKey }),
                            });

                            this.testResult = await response.json();
                        } catch (e) {
                            this.testResult = { ok: false, message: @js(__('Test request failed. Please check your connection and try again.')) };
                        } finally {
                            this.testing = false;
                        }
                    },

                    async save(url) {
                        if (! this.key) {
                            this.testResult = { ok: false, message: @js(__('Enter the new key you want to save.')) };
                            return;
                        }

                        this.saving = true;

                        try {
                            const formData = new FormData();
                            formData.append('_method', 'PUT');
                            formData.append('google_maps_api_key', this.key);

                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'text/html',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: formData,
                            });

                            if (response.redirected || response.ok) {
                                window.location.reload();
                                return;
                            }

                            this.testResult = { ok: false, message: @js(__('Save failed. Please try again.')) };
                        } catch (e) {
                            this.testResult = { ok: false, message: @js(__('Save request failed. Please check your connection and try again.')) };
                        } finally {
                            this.saving = false;
                        }
                    },
                };
            }
        </script>
    @endpush
</x-admin.layouts.app>
