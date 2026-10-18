@props(['title' => null, 'description' => null])

{{--
    Reusable "enable browser notifications on this device" control — used
    as-is on the admin, customer, and driver sides (see
    admin/notification-settings/edit.blade.php, customer/notifications/
    index.blade.php, driver/settings/edit.blade.php). Handles every state
    the brief calls for: unsupported browser, permission denied/default/
    granted, already-subscribed, and subscribe/unsubscribe round-trips to
    PushSubscriptionController — all client-side, no page reload.

    The Alpine component itself (window.pushNotificationToggle) lives in
    resources/js/app.js rather than an inline <script> here, because the
    compact topbar badge (components/push-notification-badge.blade.php)
    shares the exact same logic and the two can render on the same page. --}}
<div x-data="pushNotificationToggle(@js(config('webpush.public_key')), @js(asset('sw.js')), @js(route('push.subscribe')), @js(route('push.unsubscribe')), @js(route('push.status')), @js(['enableError' => __('Something went wrong enabling notifications. Please try again.'), 'disableError' => __('Could not disable notifications. Please try again.')]))" x-init="init()"
    {{ $attributes->merge(['class' => 'rounded-2xl border border-luxury-border bg-luxury-charcoal p-6']) }}>
    <div class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-luxury-border bg-luxury-graphite text-luxury-gold">
            <x-icon name="bell" class="h-5 w-5" />
        </span>
        <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-luxury-white">{{ $title ?? __('Browser Notifications') }}</h3>
            <p class="mt-1 text-sm text-luxury-muted">{{ $description ?? __('Get instant browser alerts about your bookings — even when this tab is closed.') }}</p>

            <template x-if="state === 'unsupported'">
                <p class="mt-3 text-xs text-luxury-muted">{{ __('Browser notifications are not supported on this browser.') }}</p>
            </template>

            <template x-if="state === 'denied'">
                <p class="mt-3 text-xs text-red-400">{{ __('Notifications are blocked for this site. Allow them from your browser\'s site settings, then reload this page.') }}</p>
            </template>

            <template x-if="state === 'enabled'">
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-400">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        {{ __('Notifications Enabled') }}
                    </span>
                    <button type="button" @click="disable()" :disabled="busy"
                        class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-red-400/40 hover:text-red-400 disabled:opacity-50">
                        <span x-show="!busy">{{ __('Disable') }}</span>
                        <span x-show="busy" x-cloak>{{ __('Please wait…') }}</span>
                    </button>
                </div>
            </template>

            <template x-if="state === 'default' || state === 'error'">
                <div class="mt-3">
                    <button type="button" @click="enable()" :disabled="busy"
                        class="tap-scale inline-flex items-center gap-2 rounded-lg bg-luxury-gold px-4 py-2 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light disabled:opacity-60">
                        <x-icon name="bell" class="h-4 w-4" />
                        <span x-show="!busy">{{ __('Enable Notifications') }}</span>
                        <span x-show="busy" x-cloak>{{ __('Enabling…') }}</span>
                    </button>
                    <p x-show="state === 'error'" x-cloak class="mt-2 text-xs text-red-400" x-text="errorMessage"></p>
                </div>
            </template>
        </div>
    </div>
</div>
