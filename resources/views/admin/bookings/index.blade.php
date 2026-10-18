<x-admin.layouts.app :title="__('Bookings')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Bookings') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage reservations across your fleet.') }}</p>
        </div>

        @permission('bookings.create')
            @if (booking_setting('manual_booking_enabled', true))
                <a href="{{ route('admin.bookings.create') }}">
                    <x-admin.button type="button" variant="primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('New Booking') }}
                    </x-admin.button>
                </a>
            @else
                <span class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted/60" title="{{ __('Manual booking is disabled in Booking Settings') }}">
                    {{ __('Manual Booking Disabled') }}
                </span>
            @endif
        @endpermission
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="status" onchange="this.form.submit()"
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2.5 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold sm:w-auto sm:py-2">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach (\App\Models\Booking::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="type" onchange="this.form.submit()"
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2.5 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold sm:w-auto sm:py-2">
            <option value="">{{ __('All Types') }}</option>
            @foreach (\App\Models\Booking::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        @if (request()->filled('status') || request()->filled('type'))
            <a href="{{ route('admin.bookings.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">{{ __('Clear filters') }}</a>
        @endif
    </form>

    <div x-data="{ selected: [] }">
        @permission('bookings.delete')
            <form method="POST" action="{{ route('admin.bookings.bulk-destroy') }}"
                @submit="if (! confirm('Delete ' + selected.length + ' selected booking(s)? This cannot be undone.')) $event.preventDefault();">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="booking_ids[]" :value="id">
                </template>
                <div x-show="selected.length > 0" x-cloak class="mb-4 flex items-center justify-between rounded-lg border border-red-500/30 bg-red-500/5 px-4 py-3">
                    <p class="text-sm text-luxury-muted"><span x-text="selected.length"></span> {{ __('booking(s) selected') }}</p>
                    <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                        {{ __('Delete Selected') }}
                    </button>
                </div>
            </form>
        @endpermission

    {{-- Desktop: full table with bulk-select --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        @permission('bookings.delete')
                            <th class="w-10 px-6 py-3">
                                <input type="checkbox" @change="selected = $event.target.checked ? {{ $bookings->pluck('id')->toJson() }} : []"
                                    class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                            </th>
                        @endpermission
                        <th class="px-6 py-3 font-medium">{{ __('Booking') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Vehicle / Driver') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Pickup') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Fare') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-luxury-graphite">
                            @permission('bookings.delete')
                                <td class="px-6 py-3">
                                    <input type="checkbox" value="{{ $booking->id }}" x-model.number="selected"
                                        class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                                </td>
                            @endpermission
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->type_label }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->customer?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">
                                <p>{{ $booking->vehicle?->name ?? '—' }}</p>
                                <p class="text-xs">{{ $booking->driver?->name ?? __('Unassigned') }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-3">
                                @permission('bookings.edit')
                                    <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()"
                                            class="rounded-full border-0 px-2.5 py-1 text-xs font-medium capitalize focus:outline-none focus:ring-1 focus:ring-luxury-gold
                                                {{ match ($booking->status) {
                                                    'completed' => 'bg-emerald-500/10 text-emerald-400',
                                                    'cancelled' => 'bg-red-500/10 text-red-400',
                                                    'assigned' => 'bg-luxury-gold/10 text-luxury-gold',
                                                    'in_progress' => 'bg-blue-500/10 text-blue-400',
                                                    'confirmed' => 'bg-luxury-secondary/10 text-luxury-secondary',
                                                    default => 'bg-luxury-slate text-luxury-muted',
                                                } }}">
                                            @foreach (\App\Models\Booking::STATUSES as $value => $label)
                                                <option value="{{ $value }}" @selected($booking->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ $booking->status_label }}</span>
                                @endpermission
                            </td>
                            <td class="px-6 py-3 text-end">
                                <p class="text-luxury-white">{{ currency($booking->fare_amount) }}</p>
                                <p class="mt-1 text-xs {{ $booking->payment_status === 'paid' ? 'text-emerald-400' : 'text-luxury-muted' }}">{{ $booking->payment_status_label }}</p>
                                @if ($booking->payment_gateway)
                                    <p class="mt-0.5 text-[11px] text-luxury-muted">{{ $booking->payment_gateway_label }} &middot; {{ $booking->transaction_id }}</p>
                                @endif
                                @if ($booking->refund_status && $booking->refund_status !== 'not_applicable')
                                    <p class="mt-0.5 text-xs {{ $booking->refund_status === 'refunded' ? 'text-emerald-400' : 'text-luxury-gold' }}">{{ $booking->refund_status_label }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ __('View') }}
                                    </a>
                                    <a href="{{ route('booking.invoice', $booking->booking_number) }}" target="_blank" rel="noopener" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ __('Invoice') }}
                                    </a>
                                    @php $waLink = $whatsapp->linkFor($booking); @endphp
                                    @if ($waLink)
                                        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/30 px-3 py-1.5 text-xs font-medium text-emerald-400 transition hover:bg-emerald-500/10">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2.05 22l5.25-1.38a9.9 9.9 0 004.74 1.21h.005c5.46 0 9.9-4.45 9.9-9.92 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0012.04 2zm5.8 14.03c-.24.68-1.4 1.3-1.94 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.96-.31-1.65-.6-2.9-1.25-4.8-4.17-4.94-4.36-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.01-2.41.27-.29.58-.36.78-.36.19 0 .39 0 .55.01.18.01.42-.07.65.5.24.58.82 2 .89 2.15.07.15.12.32.02.51-.09.19-.14.31-.28.48-.14.16-.29.36-.42.48-.14.13-.29.28-.13.55.17.28.74 1.22 1.6 1.98 1.1.97 2.02 1.28 2.31 1.42.29.14.46.12.63-.07.17-.19.72-.83.91-1.11.19-.29.38-.24.63-.14.26.1 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/>
                                            </svg>
                                            {{ __('Book Now') }}
                                        </a>
                                    @endif
                                    @permission('bookings.edit')
                                        @if ($booking->payment_status !== 'paid')
                                            <form method="POST" action="{{ route('admin.bookings.mark-paid', $booking) }}" class="flex items-center gap-1.5">
                                                @csrf
                                                <select name="payment_gateway" class="rounded-lg border border-luxury-border bg-luxury-charcoal px-2 py-1.5 text-xs text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                                    @foreach (\App\Models\Booking::PAYMENT_GATEWAYS as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-emerald-500/40 hover:text-emerald-400">
                                                    {{ __('Mark Paid') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($booking->refund_status === 'pending')
                                            <form method="POST" action="{{ route('admin.bookings.process-refund', $booking) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-emerald-500/40 hover:text-emerald-400">
                                                    {{ __('Process Refund') }}
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.bookings.edit', $booking) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                    @endpermission
                                    @permission('bookings.delete')
                                        <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('{{ __('Delete this booking?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-luxury-muted">{{ __('No bookings found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards. Bulk-select and the inline Mark Paid / Process
         Refund forms stay desktop-only here for space — every booking is
         still individually reachable via View/Edit/Delete below. --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($bookings as $booking)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->booking_number }}</p>
                        <p class="mt-0.5 truncate text-xs text-luxury-muted">{{ $booking->customer?->name ?? '—' }} &middot; {{ $booking->type_label }}</p>
                    </div>
                    <span class="shrink-0 text-sm font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</span>
                </div>

                <div class="mt-3 flex items-start gap-2 border-t border-luxury-border pt-3">
                    <x-icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-luxury-gold" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs text-luxury-muted">{{ $booking->pickup_location }} &rarr; {{ $booking->dropoff_location }}</p>
                        <p class="mt-0.5 text-[11px] text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3 border-t border-luxury-border pt-3 text-xs">
                    <span class="text-luxury-muted">{{ $booking->vehicle?->name ?? '—' }} &middot; {{ $booking->driver?->name ?? __('Unassigned') }}</span>
                    <span class="{{ $booking->payment_status === 'paid' ? 'text-emerald-400' : 'text-luxury-muted' }}">{{ $booking->payment_status_label }}</span>
                </div>

                <div class="mt-3 border-t border-luxury-border pt-3">
                    @permission('bookings.edit')
                        <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                            @csrf
                            <select name="status" onchange="this.form.submit()"
                                class="w-full rounded-lg border-0 px-2.5 py-2 text-xs font-medium capitalize focus:outline-none focus:ring-1 focus:ring-luxury-gold
                                    {{ match ($booking->status) {
                                        'completed' => 'bg-emerald-500/10 text-emerald-400',
                                        'cancelled' => 'bg-red-500/10 text-red-400',
                                        'assigned' => 'bg-luxury-gold/10 text-luxury-gold',
                                        'in_progress' => 'bg-blue-500/10 text-blue-400',
                                        'confirmed' => 'bg-luxury-secondary/10 text-luxury-secondary',
                                        default => 'bg-luxury-slate text-luxury-muted',
                                    } }}">
                                @foreach (\App\Models\Booking::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected($booking->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <span class="inline-block rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ $booking->status_label }}</span>
                    @endpermission
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    <a href="{{ route('admin.bookings.show', $booking) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        {{ __('View') }}
                    </a>
                    @php $waLinkMobile = $whatsapp->linkFor($booking); @endphp
                    @if ($waLinkMobile)
                        <a href="{{ $waLinkMobile }}" target="_blank" rel="noopener" class="tap-scale rounded-lg border border-emerald-500/30 px-3 py-1.5 text-xs font-medium text-emerald-400 transition hover:bg-emerald-500/10">
                            {{ __('WhatsApp') }}
                        </a>
                    @endif
                    @permission('bookings.edit')
                        <a href="{{ route('admin.bookings.edit', $booking) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Edit') }}
                        </a>
                    @endpermission
                    @permission('bookings.delete')
                        <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('{{ __('Delete this booking?') }}');" class="ms-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tap-scale rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    @endpermission
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No bookings found.') }}
            </div>
        @endforelse
    </div>
    </div>
</x-admin.layouts.app>
