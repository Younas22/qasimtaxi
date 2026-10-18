@php
    $cfg = $gateway->config();
@endphp

<x-admin.layouts.app :title="__('Configure :name', ['name' => $gateway->name])">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Configure :name', ['name' => $gateway->name]) }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage credentials, sandbox/live mode, and availability for :name.', ['name' => $gateway->name]) }}</p>
    </div>

    <form method="POST" action="{{ route('admin.payment-gateways.update', $gateway) }}" x-data="{ mode: {{ \Illuminate\Support\Js::from(old('mode', $gateway->mode)) }}, reveal: {} }">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <x-admin.toggle
                    name="is_enabled"
                    :checked="old('is_enabled', $gateway->is_enabled)"
                    label="{{ __('Enable :name', ['name' => $gateway->name]) }}"
                    description="{{ __("Customers can only use this gateway at checkout while it's enabled and both keys for the active mode are configured.") }}" />

                <x-admin.input-error :messages="$errors->get('is_enabled')" />

                <div>
                    <x-admin.input-label value="{{ __('Mode') }}" />
                    <div class="flex flex-wrap gap-3">
                        <label class="flex items-center gap-2 rounded-lg border border-luxury-border px-4 py-2.5 text-sm text-luxury-muted has-[:checked]:border-luxury-secondary has-[:checked]:text-luxury-secondary">
                            <input type="radio" name="mode" value="sandbox" x-model="mode" class="h-4 w-4 border-luxury-border bg-luxury-charcoal text-luxury-secondary focus:ring-1 focus:ring-luxury-secondary">
                            {{ __('Sandbox') }}
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-luxury-border px-4 py-2.5 text-sm text-luxury-muted has-[:checked]:border-red-400 has-[:checked]:text-red-400">
                            <input type="radio" name="mode" value="live" x-model="mode" class="h-4 w-4 border-luxury-border bg-luxury-charcoal text-red-400 focus:ring-1 focus:ring-red-400">
                            {{ __('Live') }}
                        </label>
                    </div>
                    <x-admin.input-error :messages="$errors->get('mode')" />
                </div>
            </div>

            {{-- Sandbox Keys --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="mode === 'sandbox'" x-cloak>
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Sandbox Credentials') }}</h3>

                <div>
                    <x-admin.input-label for="sandbox_key_1" value="{{ $cfg['key_1_label'] ?? __('Key 1') }}" />
                    <x-admin.text-input id="sandbox_key_1" name="sandbox_key_1" type="text"
                        placeholder="{{ $gateway->sandbox_key_1 ? __('•••••••••••••••• (unchanged)') : ($cfg['sandbox_key_1_placeholder'] ?? '') }}" />
                    <x-admin.input-error :messages="$errors->get('sandbox_key_1')" />
                </div>

                <div>
                    <x-admin.input-label for="sandbox_key_2" value="{{ $cfg['key_2_label'] ?? __('Key 2') }}" />
                    <div class="flex items-center gap-2">
                        <input :type="reveal.sandbox_key_2 ? 'text' : 'password'" id="sandbox_key_2" name="sandbox_key_2" autocomplete="new-password"
                            placeholder="{{ $gateway->sandbox_key_2 ? __('•••••••••••••••• (unchanged)') : ($cfg['sandbox_key_2_placeholder'] ?? '') }}"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        <button type="button" @click="reveal.sandbox_key_2 = !reveal.sandbox_key_2" class="shrink-0 rounded-lg border border-luxury-border px-3 py-3 text-xs text-luxury-muted hover:text-luxury-gold">
                            <span x-text="reveal.sandbox_key_2 ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
                        </button>
                    </div>
                    <x-admin.input-error :messages="$errors->get('sandbox_key_2')" />
                </div>
            </div>

            {{-- Live Keys --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="mode === 'live'" x-cloak>
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Live Credentials') }}</h3>

                <div>
                    <x-admin.input-label for="live_key_1" value="{{ $cfg['key_1_label'] ?? __('Key 1') }}" />
                    <x-admin.text-input id="live_key_1" name="live_key_1" type="text"
                        placeholder="{{ $gateway->live_key_1 ? __('•••••••••••••••• (unchanged)') : ($cfg['live_key_1_placeholder'] ?? '') }}" />
                    <x-admin.input-error :messages="$errors->get('live_key_1')" />
                </div>

                <div>
                    <x-admin.input-label for="live_key_2" value="{{ $cfg['key_2_label'] ?? __('Key 2') }}" />
                    <div class="flex items-center gap-2">
                        <input :type="reveal.live_key_2 ? 'text' : 'password'" id="live_key_2" name="live_key_2" autocomplete="new-password"
                            placeholder="{{ $gateway->live_key_2 ? __('•••••••••••••••• (unchanged)') : ($cfg['live_key_2_placeholder'] ?? '') }}"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        <button type="button" @click="reveal.live_key_2 = !reveal.live_key_2" class="shrink-0 rounded-lg border border-luxury-border px-3 py-3 text-xs text-luxury-muted hover:text-luxury-gold">
                            <span x-text="reveal.live_key_2 ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
                        </button>
                    </div>
                    <x-admin.input-error :messages="$errors->get('live_key_2')" />
                </div>
            </div>

            <p class="text-xs text-luxury-muted">
                {{ __('Leave a field blank to keep its currently saved value. Credentials are stored encrypted at rest.') }}
            </p>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.payment-gateways.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Cancel') }}
            </a>
            <x-admin.button type="submit" variant="primary">{{ __('Save Gateway') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
