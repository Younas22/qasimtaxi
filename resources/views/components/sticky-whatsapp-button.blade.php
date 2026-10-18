@php
    $whatsappDigits = setting('whatsapp') ? preg_replace('/\D+/', '', setting('whatsapp')) : null;
@endphp

@if ($whatsappDigits)
    {{-- Mobile has a WhatsApp entry in the bottom tab bar instead — this floating button is desktop-only to avoid duplicating it right above that bar. --}}
    <a href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode(__("Hi! I'd like to inquire about booking a ride.")) }}" target="_blank" rel="noopener" aria-label="{{ __('Chat on WhatsApp') }}"
        class="tap-scale fixed bottom-8 start-8 z-30 hidden h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl shadow-black/30 transition hover:brightness-105 hover:shadow-2xl lg:flex">
        <x-whatsapp-icon class="h-7 w-7" />
    </a>
@endif
