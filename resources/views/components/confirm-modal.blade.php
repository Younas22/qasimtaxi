@props([
    'action',
    'method' => 'POST',
    'title',
    'message',
    'confirmLabel' => null,
    'triggerLabel',
    'triggerClass' => 'text-xs font-medium text-red-400 hover:text-red-300',
    'triggerIcon' => null,
])

{{--
    Shared styled confirmation dialog — replaces a plain browser confirm()
    for a destructive action (revoke sessions, remove an address, close a
    ticket) with something that matches the rest of the app, while keeping
    the same keyboard/focus behaviour a native confirm() gives for free:
    Escape closes it, the confirm button is reachable by keyboard, and
    focus never silently escapes to the page behind it.
--}}
<div x-data="{ open: false }" class="contents">
    <button type="button" @click="open = true" class="tap-scale {{ $triggerClass }}">
        @if ($triggerIcon)
            <x-icon :name="$triggerIcon" class="h-3.5 w-3.5" />
        @endif
        {{ $triggerLabel }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-[70] flex items-end justify-center bg-black/70 sm:items-center sm:p-4"
        x-transition.opacity @keydown.escape.window="open = false">
        <div @click.outside="open = false" x-show="open"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            class="w-full max-w-sm rounded-t-2xl border border-luxury-border bg-luxury-charcoal p-6 sm:rounded-2xl"
            role="alertdialog" aria-modal="true" :aria-hidden="!open">
            <h3 class="text-lg font-semibold text-luxury-white">{{ $title }}</h3>
            <p class="mt-1 text-sm text-luxury-muted">{{ $message }}</p>

            <div class="mt-5 flex items-center gap-3">
                <form method="POST" action="{{ $action }}" class="flex-1">
                    @csrf
                    @unless (strtoupper($method) === 'POST')
                        @method($method)
                    @endunless
                    <button type="submit" x-ref="confirmButton" @keydown.escape.window="open = false"
                        class="tap-scale flex w-full items-center justify-center rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-400 transition hover:bg-red-500/20">
                        {{ $confirmLabel ?? __('Confirm') }}
                    </button>
                </form>
                <button type="button" @click="open = false"
                    class="tap-scale rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:text-luxury-white">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
