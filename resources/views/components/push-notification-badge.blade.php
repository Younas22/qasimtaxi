{{--
    Compact "enable browser notifications" pill for the topbar — admin,
    customer, and driver each include this right in their header (see
    admin/partials/topbar.blade.php, customer/partials/topbar.blade.php,
    driver/partials/topbar.blade.php), so turning notifications on is
    something you run into on every page rather than something you have to
    know to go dig for in a settings screen. Shares the same Alpine
    component (window.pushNotificationToggle, in resources/js/app.js) as the
    full settings card, just with a one-line template — only ever visible
    when there's actually an action to take (permission not yet asked for,
    or a previous attempt errored); silent once enabled, unsupported, or
    blocked, since a topbar nag has no useful next step in those states.
--}}
<div x-data="pushNotificationToggle(@js(config('webpush.public_key')), @js(asset('sw.js')), @js(route('push.subscribe')), @js(route('push.unsubscribe')), @js(route('push.status')), @js(['enableError' => __('Something went wrong enabling notifications. Please try again.'), 'disableError' => __('Could not disable notifications. Please try again.')]))" x-init="init()">
    <template x-if="state === 'default' || state === 'error'">
        <button type="button" @click="enable()" :disabled="busy"
            class="tap-scale inline-flex h-9 items-center gap-1.5 whitespace-nowrap rounded-lg border border-luxury-gold/40 bg-luxury-gold/10 px-2.5 text-xs font-semibold text-luxury-gold transition hover:bg-luxury-gold/20 disabled:opacity-60"
            title="{{ __('Get instant browser alerts about your bookings — even when this tab is closed.') }}">
            <x-icon name="bell" class="h-4 w-4 shrink-0" />
            <span class="hidden sm:inline" x-text="busy ? '{{ __('Enabling…') }}' : '{{ __('Enable Notifications') }}'"></span>
        </button>
    </template>
</div>
