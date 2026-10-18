@php
    // "Push" was dropped from this table on purpose — it used to be a
    // locked/"Soon" placeholder column here (push_enabled was never wired
    // to an actual provider), but real browser push now has its own full
    // Master/Role/Event-type control panel just above this table (see the
    // "Browser Push Notifications" section), which fully supersedes it for
    // every one of these 5 events. Keeping both would show the same
    // capability as simultaneously "Soon" and "already working", which is
    // exactly the confusion this removal avoids.
    $channels = [
        'email_enabled' => __('Email'),
        'admin_panel_enabled' => __('Admin Panel'),
        'sms_enabled' => __('SMS'),
    ];
    $futureChannels = ['sms_enabled'];
@endphp

<x-admin.layouts.app :title="__('Notification Preferences')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Notification Preferences') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Choose which channels deliver each booking event, and control browser push notifications across Admin, Customers, and Drivers.') }}</p>
    </div>

    {{-- Enable browser notifications on THIS admin's own browser --}}
    <x-push-notification-toggle class="mb-6" :description="__('Get instant browser alerts for bookings, payments, and driver activity.')" />

    {{-- Browser Push Notifications — master + role + granular event controls --}}
    <form method="POST" action="{{ route('admin.push-settings.update') }}" enctype="multipart/form-data" class="mb-8 space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        @csrf
        @method('PUT')

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-luxury-white">{{ __('Browser Push Notifications') }}</h3>
                <p class="mt-1 text-sm text-luxury-muted">{{ __('Control browser push notifications across Admin, Customers and Drivers.') }}</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-[11px] font-medium uppercase tracking-wide {{ $pushSettings->push_notifications_enabled ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                {{ $pushSettings->push_notifications_enabled ? __('Enabled') : __('Disabled') }}
            </span>
        </div>

        <x-admin.toggle name="push_notifications_enabled" :checked="$pushSettings->push_notifications_enabled"
            label="{{ __('Enable Browser Push Notifications') }}"
            description="{{ __('Master switch — while OFF, no browser push is sent to anyone, regardless of the settings below.') }}" />

        <div class="border-t border-luxury-border pt-5">
            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-luxury-muted">{{ __('Recipients') }}</h4>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <x-admin.toggle name="push_admin_enabled" :checked="$pushSettings->push_admin_enabled"
                    label="{{ __('Admin') }}" description="{{ __('Receive browser push notifications') }}" />
                <x-admin.toggle name="push_customer_enabled" :checked="$pushSettings->push_customer_enabled"
                    label="{{ __('Customer') }}" description="{{ __('Receive browser push notifications') }}" />
                <x-admin.toggle name="push_driver_enabled" :checked="$pushSettings->push_driver_enabled"
                    label="{{ __('Driver') }}" description="{{ __('Receive browser push notifications') }}" />
            </div>
        </div>

        <div class="border-t border-luxury-border pt-5">
            <h4 class="mb-1 text-xs font-semibold uppercase tracking-wider text-luxury-muted">{{ __('Notification Sound') }}</h4>
            <p class="mb-3 text-xs text-luxury-muted">
                {{ __("Plays in any open browser tab of this app when a notification arrives. When no tab is open, the browser's own default sound plays instead — no site can override that.") }}
            </p>

            <div x-data="{ fileName: null }" class="flex flex-col gap-3 rounded-xl border border-luxury-border bg-luxury-graphite/40 p-4 sm:flex-row sm:items-center">
                @if ($pushSettings->notification_sound_url)
                    <audio controls preload="none" src="{{ $pushSettings->notification_sound_url }}" class="h-9 w-full sm:w-64"></audio>
                @else
                    <p class="text-xs text-luxury-muted">{{ __('No custom sound uploaded — using the browser default.') }}</p>
                @endif

                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-2.5 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span x-text="fileName ?? '{{ __('Click to upload a sound (mp3/wav/ogg, max 1MB)') }}'"></span>
                    <input type="file" name="notification_sound" accept="audio/*" class="hidden"
                        @change="fileName = $event.target.files.length ? $event.target.files[0].name : null">
                </label>

                @if ($pushSettings->notification_sound_url)
                    <label class="flex shrink-0 cursor-pointer items-center gap-2 text-xs text-luxury-muted">
                        <input type="checkbox" name="remove_notification_sound" value="1"
                            class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-red-400 focus:ring-red-400 focus:ring-offset-luxury-black">
                        {{ __('Remove') }}
                    </label>
                @endif
            </div>
            <x-admin.input-error :messages="$errors->get('notification_sound')" />
        </div>

        @php
            $eventGroups = [
                __('Admin Events') => \App\Models\PushNotificationSetting::ADMIN_EVENTS,
                __('Driver Events') => \App\Models\PushNotificationSetting::DRIVER_EVENTS,
                __('Customer Events') => \App\Models\PushNotificationSetting::CUSTOMER_EVENTS,
            ];
        @endphp

        <div class="grid grid-cols-1 gap-5 border-t border-luxury-border pt-5 lg:grid-cols-3">
            @foreach ($eventGroups as $groupLabel => $events)
                <div class="rounded-xl border border-luxury-border/70 bg-luxury-graphite/40 p-4">
                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-luxury-muted">{{ $groupLabel }}</h4>
                    <div class="space-y-2.5">
                        @foreach ($events as $column => $label)
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-luxury-white">
                                <input type="hidden" name="{{ $column }}" value="0">
                                <input type="checkbox" name="{{ $column }}" value="1" @checked($pushSettings->{$column})
                                    class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold focus:ring-offset-luxury-black">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-luxury-border pt-5">
            {{-- Belongs to #push-test-form below (a sibling, not nested — two
                 <form> elements can't nest in valid HTML) via the form="" attribute. --}}
            <button type="submit" form="push-test-form" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Send Test Notification') }}
            </button>
            <x-admin.button type="submit" variant="primary">{{ __('Save Notification Settings') }}</x-admin.button>
        </div>
    </form>

    <form id="push-test-form" method="POST" action="{{ route('admin.push-settings.test') }}" class="hidden">
        @csrf
    </form>

    <form method="POST" action="{{ route('admin.notification-settings.update') }}">
        @csrf
        @method('PUT')

        <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead>
                        <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                            <th class="px-6 py-3 font-medium">{{ __('Event') }}</th>
                            @foreach ($channels as $field => $label)
                                <th class="px-6 py-3 text-center font-medium">
                                    {{ $label }}
                                    @if (in_array($field, $futureChannels, true))
                                        <span class="ms-1 rounded-full bg-luxury-slate px-1.5 py-0.5 text-[9px] normal-case text-luxury-muted">{{ __('Soon') }}</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-luxury-border/60">
                        @foreach ($settings as $setting)
                            <tr class="hover:bg-luxury-graphite">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-luxury-white">{{ $setting->label }}</p>
                                </td>
                                @foreach ($channels as $field => $label)
                                    @php $isFuture = in_array($field, $futureChannels, true); @endphp
                                    <td class="px-6 py-4 text-center">
                                        <label class="relative inline-flex items-center {{ $isFuture ? 'cursor-not-allowed opacity-40' : 'cursor-pointer' }}">
                                            <input type="checkbox"
                                                name="settings[{{ $setting->event_type }}][{{ $field }}]"
                                                value="1"
                                                @checked($setting->{$field})
                                                @disabled($isFuture)
                                                class="peer sr-only">
                                            <span class="h-6 w-11 rounded-full bg-luxury-slate transition peer-checked:bg-luxury-gold"></span>
                                            <span class="absolute start-1 top-1 h-4 w-4 rounded-full bg-luxury-white transition-transform peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.notifications.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Cancel') }}
            </a>
            <x-admin.button type="submit" variant="primary">{{ __('Save Preferences') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
