@props(['section'])

@php $fields = $section->vision_mission; @endphp

@if ($fields['vision_body'] || $fields['mission_body'])
    <section class="border-b border-luxury-border">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            @if ($section->heading || $section->subheading)
                <div class="mx-auto max-w-2xl text-center">
                    @if ($section->heading)
                        <h2 class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</h2>
                    @endif
                    @if ($section->subheading)
                        <p class="mt-3 text-luxury-muted">{{ __($section->subheading) }}</p>
                    @endif
                </div>
            @endif

            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2">
                @if ($fields['vision_body'])
                    <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-8">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold">
                            <x-icon :name="$fields['vision_icon']" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-lg font-semibold text-luxury-white">{{ __($fields['vision_title']) }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-luxury-muted">{{ __($fields['vision_body']) }}</p>
                    </div>
                @endif

                @if ($fields['mission_body'])
                    <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-8">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold">
                            <x-icon :name="$fields['mission_icon']" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-lg font-semibold text-luxury-white">{{ __($fields['mission_title']) }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-luxury-muted">{{ __($fields['mission_body']) }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
