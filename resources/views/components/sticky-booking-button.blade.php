{{-- Desktop-only floating CTA; mobile gets the raised center button in the bottom nav instead. --}}
<a href="{{ route('pages.home') }}#booking-widget"
    class="tap-scale fixed bottom-8 end-8 z-30 hidden items-center gap-2.5 rounded-full bg-luxury-gold px-6 py-4 text-sm font-semibold text-luxury-black shadow-xl shadow-black/30 transition hover:bg-luxury-gold-light hover:shadow-2xl lg:flex">
    <x-icon name="calendar" class="h-5 w-5" />
    {{ __('Book Now') }}
</a>
