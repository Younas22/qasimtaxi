@props(['booking', 'buttonClass' => 'tap-scale inline-flex items-center gap-1.5 rounded-lg border border-red-500/30 px-3 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10', 'buttonLabel' => null])

<div x-data="{ open: false, reason: '' }" class="contents">
    <button type="button" @click="open = true" class="{{ $buttonClass }}">
        <x-icon name="close" class="h-3.5 w-3.5" />
        {{ $buttonLabel ?? __('Cancel') }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-[70] flex items-end justify-center bg-black/70 sm:items-center sm:p-4"
        x-transition.opacity @keydown.escape.window="open = false">
        <div @click.outside="open = false" x-show="open"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            class="w-full max-w-md rounded-t-2xl border border-luxury-border bg-luxury-charcoal p-6 sm:rounded-2xl">
            <h3 class="text-lg font-semibold text-luxury-white">{{ __('Cancel Booking') }}</h3>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Cancellations within 24 hours of pickup may be subject to a fee.') }}</p>

            <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label class="mb-2 block text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ __('Reason for cancelling') }}</label>
                    <select name="cancellation_reason" x-model="reason" required
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        <option value="" disabled selected>{{ __('Select a reason...') }}</option>
                        @foreach (\App\Models\Booking::CANCELLATION_REASONS as $value => $label)
                            <option value="{{ $value }}">{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="reason === 'other'" x-cloak>
                    <label class="mb-2 block text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ __('Tell us more (optional)') }}</label>
                    <textarea name="cancellation_note" rows="3" maxlength="500"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="submit" class="tap-scale flex-1 rounded-lg bg-red-500/10 border border-red-500/30 px-4 py-2.5 text-sm font-semibold text-red-400 transition hover:bg-red-500/20">
                        {{ __('Confirm Cancellation') }}
                    </button>
                    <button type="button" @click="open = false" class="tap-scale rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:text-luxury-white">
                        {{ __('Keep Booking') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
