@props(['from', 'to'])

<div class="flex flex-wrap items-end gap-3">
    <div>
        <label class="mb-1 block text-xs text-luxury-muted">{{ __('From') }}</label>
        <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
    </div>
    <div>
        <label class="mb-1 block text-xs text-luxury-muted">{{ __('To') }}</label>
        <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
    </div>

    {{ $slot }}

    <button type="submit" class="rounded-lg bg-luxury-gold px-4 py-2 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
        {{ __('Apply') }}
    </button>
</div>
