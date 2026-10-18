@props(['url', 'title'])

<div x-data="{ copied: false, url: {{ \Illuminate\Support\Js::from($url) }} }" class="flex flex-wrap items-center gap-2">
    <span class="text-xs font-medium uppercase tracking-wide text-luxury-muted">{{ __('Share') }}</span>

    <a href="https://wa.me/?text={{ urlencode($title.' '.$url) }}" target="_blank" rel="noopener"
        aria-label="{{ __('Share on WhatsApp') }}"
        class="flex h-9 w-9 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <x-icon name="chat" class="h-4 w-4" />
    </a>

    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" target="_blank" rel="noopener"
        aria-label="{{ __('Share on Facebook') }}"
        class="flex h-9 w-9 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.51 1.49-3.9 3.77-3.9 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94z"/>
        </svg>
    </a>

    <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($title) }}" target="_blank" rel="noopener"
        aria-label="{{ __('Share on X') }}"
        class="flex h-9 w-9 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.22-6.82-5.97 6.82H1.66l7.73-8.83L1.25 2.25h6.83l4.72 6.24 5.44-6.24zm-1.16 17.52h1.83L7.02 4.13H5.06l12.02 15.64z"/>
        </svg>
    </a>

    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($url) }}" target="_blank" rel="noopener"
        aria-label="{{ __('Share on LinkedIn') }}"
        class="flex h-9 w-9 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.03-1.85-3.03-1.86 0-2.15 1.45-2.15 2.94v5.66H9.35V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 11.02-4.12 2.06 2.06 0 01-.02 4.12zM7.11 20.45H3.56V9h3.55v11.45z"/>
        </svg>
    </a>

    <button type="button" @click="navigator.clipboard.writeText(url); copied = true; setTimeout(() => copied = false, 2000)"
        aria-label="{{ __('Copy link') }}"
        class="flex h-9 w-9 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <template x-if="!copied">
            <x-icon name="link" class="h-4 w-4" />
        </template>
        <template x-if="copied">
            <x-icon name="check-circle" class="h-4 w-4 text-luxury-gold" />
        </template>
    </button>
    <span x-show="copied" x-cloak x-transition class="text-xs text-luxury-gold">{{ __('Link copied!') }}</span>
</div>
