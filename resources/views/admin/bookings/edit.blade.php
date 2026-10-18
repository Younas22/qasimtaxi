@php $waLink = $whatsapp->linkFor($booking); @endphp

<x-admin.layouts.app :title="__('Edit Booking')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Edit Booking') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ $booking->booking_number }}</p>
        </div>

        @if ($waLink)
            <a href="{{ $waLink }}" target="_blank" rel="noopener">
                <x-admin.button type="button" variant="secondary">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2.05 22l5.25-1.38a9.9 9.9 0 004.74 1.21h.005c5.46 0 9.9-4.45 9.9-9.92 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0012.04 2zm5.8 14.03c-.24.68-1.4 1.3-1.94 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.96-.31-1.65-.6-2.9-1.25-4.8-4.17-4.94-4.36-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.01-2.41.27-.29.58-.36.78-.36.19 0 .39 0 .55.01.18.01.42-.07.65.5.24.58.82 2 .89 2.15.07.15.12.32.02.51-.09.19-.14.31-.28.48-.14.16-.29.36-.42.48-.14.13-.29.28-.13.55.17.28.74 1.22 1.6 1.98 1.1.97 2.02 1.28 2.31 1.42.29.14.46.12.63-.07.17-.19.72-.83.91-1.11.19-.29.38-.24.63-.14.26.1 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/>
                    </svg>
                    {{ __('Book Now via WhatsApp') }}
                </x-admin.button>
            </a>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
        @csrf
        @method('PUT')

        @include('admin.bookings._form', ['booking' => $booking])

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.bookings.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Cancel') }}
            </a>
            <x-admin.button type="submit" variant="primary">{{ __('Update Booking') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
