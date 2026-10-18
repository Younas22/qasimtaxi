@props(['section'])

@php $members = $section->team_members; @endphp

@if (! empty($members))
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

            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                x-data="{ armed: false, visible: false }" x-init="armed = true" x-intersect.once="visible = true">
                @foreach ($members as $index => $member)
                    <div class="reveal-up delay-{{ ($index % 6) + 1 }} text-center" :class="{ 'reveal-armed': armed, 'is-visible': visible }">
                        <div class="mx-auto h-28 w-28 overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                            @if (! empty($member['photo']))
                                <x-lazy-image :src="asset('public/uploads/team/'.$member['photo'])" :alt="$member['name']" class="rounded-full" />
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl font-semibold text-luxury-gold">
                                    {{ strtoupper(substr($member['name'] ?? '?', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <p class="mt-4 font-semibold text-luxury-white">{{ $member['name'] ?? '' }}</p>
                        @if (! empty($member['role']))
                            <p class="text-xs font-medium uppercase tracking-wide text-luxury-gold">{{ $member['role'] }}</p>
                        @endif
                        @if (! empty($member['bio']))
                            <p class="mt-2 text-sm text-luxury-muted">{{ $member['bio'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
