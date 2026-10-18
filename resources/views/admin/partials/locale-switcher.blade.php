@php
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
@endphp

@if ($currentLanguage && $activeLanguages->count() > 1)
    <div x-data="{ open: false }" class="relative inline-block">
        <button type="button" @click="open = !open" @click.outside="open = false"
            class="tap-scale flex h-9 w-full items-center gap-1.5 whitespace-nowrap rounded-lg border border-luxury-border px-2.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-white">
            @if ($currentLanguage->flag_country_code)
                <span class="fi fi-{{ $currentLanguage->flag_country_code }} shrink-0 rounded-sm"></span>
            @endif
            <span>{{ $currentLanguage->native_name ?: $currentLanguage->name }}</span>
            <x-icon name="chevron-down" class="h-3.5 w-3.5" />
        </button>

        <div x-show="open" x-cloak x-transition
            class="absolute end-0 top-full z-30 w-full overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
            @foreach ($activeLanguages as $language)
                <form method="POST" action="{{ route('admin.locale.switch', $language->code) }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-2 px-3.5 py-2.5 text-start text-xs {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
                        @if ($language->flag_country_code)
                            <span class="fi fi-{{ $language->flag_country_code }} shrink-0 rounded-sm"></span>
                        @endif
                        {{ $language->native_name ?: $language->name }}
                    </button>
                </form>
            @endforeach
        </div>
    </div>
@endif
