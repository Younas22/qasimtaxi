@php $driver = auth()->guard('driver')->user(); @endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.outside="open = false"
        class="tap-scale flex items-center gap-2 rounded-lg border border-luxury-border py-1.5 ps-1.5 pe-3 transition hover:border-luxury-gold/40">
        <img src="{{ $driver?->photo_url }}" alt="{{ $driver?->name }}"
            class="h-7 w-7 rounded-md object-cover" onerror="this.src='https://ui-avatars.com/api/?background=c9a24b&color=0a0a0a&name={{ urlencode($driver?->name ?? 'Driver') }}'">
        <span class="hidden text-sm font-medium text-luxury-white sm:inline">{{ $driver?->name }}</span>
        <x-icon name="chevron-down" class="h-4 w-4 text-luxury-muted" />
    </button>

    <div x-show="open" x-cloak x-transition
        class="absolute end-0 z-30 mt-2 w-56 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
        <div class="border-b border-luxury-border px-4 py-3">
            <p class="text-sm font-semibold text-luxury-white">{{ $driver?->name }}</p>
            <p class="mt-0.5 truncate text-xs text-luxury-muted">{{ $driver?->email }}</p>
        </div>

        <div class="py-1">
            <a href="{{ route('driver.profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white">
                <x-icon name="user" class="h-4 w-4" />
                {{ __('Profile Settings') }}
            </a>
            <a href="{{ route('driver.security.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white">
                <x-icon name="lock" class="h-4 w-4" />
                {{ __('Security') }}
            </a>
        </div>

        <form method="POST" action="{{ route('driver.logout') }}" class="border-t border-luxury-border">
            @csrf
            <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-start text-sm text-red-400 hover:bg-luxury-graphite">
                {{ __('Sign Out') }}
            </button>
        </form>
    </div>
</div>
