@props(['booking', 'dispatch', 'pollUrl' => null])

<div x-data="dispatchCard(@js($dispatch), @js($pollUrl))" x-init="init()" class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="mb-1 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Driver Dispatch') }}</h3>
        <span class="rounded-full px-2.5 py-1 text-xs font-medium"
            :class="{
                'bg-blue-500/10 text-blue-400': status === 'busy',
                'bg-luxury-gold/10 text-luxury-gold': status === 'in_progress',
                'bg-emerald-500/10 text-emerald-400': status === 'available',
            }"
            x-text="status === 'busy' ? '{{ __('On Trip') }}' : (status === 'in_progress' ? '{{ __('In Progress') }}' : '{{ __('Available') }}')"></span>
    </div>

    {{-- Live-update state — never lets stale data quietly look fresh. --}}
    <p class="mb-4 text-[11px] text-luxury-muted" aria-live="polite">
        <template x-if="pollFailed">
            <span class="text-amber-400">{{ __('Connection interrupted — retrying…') }}</span>
        </template>
        <template x-if="!pollFailed && pollUrl">
            <span x-text="lastUpdatedLabel"></span>
        </template>
    </p>

    <template x-if="!data">
        <p class="text-sm text-luxury-muted">{{ __('Dispatch information is not available yet.') }}</p>
    </template>

    <template x-if="data && scenario === 4">
        <div class="space-y-4">
            <p class="text-sm text-luxury-muted" x-text="data.message"></p>

            <template x-if="data.driver">
                <div class="flex items-center gap-3 rounded-xl bg-luxury-graphite/40 p-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-luxury-white" x-text="data.driver.name"></p>
                        <p class="truncate text-xs text-luxury-muted" x-text="data.driver.vehicle || ''"></p>
                    </div>
                    <a :href="'tel:' + data.driver.phone" x-show="data.driver.phone"
                        class="tap-scale flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        <x-icon name="phone" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </template>
        </div>
    </template>

    <template x-if="data && scenario !== 3 && scenario !== 4">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Dispatching From') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white" x-text="data.dispatch_location_label"></p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Distance') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white" x-text="data.distance_km !== null ? data.distance_km + ' km' : '—'"></p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('ETA') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-gold" x-text="data.duration_minutes !== null ? data.duration_minutes + ' {{ __('min') }}' : '—'"></p>
                </div>
            </div>

            <p class="text-sm text-luxury-muted" x-text="data.message"></p>

            <template x-if="data.driver">
                <div class="flex items-center gap-3 rounded-xl bg-luxury-graphite/40 p-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-luxury-white" x-text="data.driver.name"></p>
                        <p class="truncate text-xs text-luxury-muted" x-text="data.driver.vehicle || ''"></p>
                    </div>
                    <a :href="'tel:' + data.driver.phone" x-show="data.driver.phone"
                        class="tap-scale flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        <x-icon name="phone" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </template>
        </div>
    </template>

    <template x-if="data && scenario === 3">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Current Ride') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white" x-text="data.current_ride_label"></p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Ride Ends In') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-gold" x-text="data.ride_ends_in_minutes !== null ? data.ride_ends_in_minutes + ' {{ __('min') }}' : '—'"></p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Current Driver Distance') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white" x-text="data.current_driver_distance_km !== null ? data.current_driver_distance_km + ' km {{ __('away') }}' : '—'"></p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Estimated Arrival After Dispatch') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-gold" x-text="data.arrival_after_dispatch_minutes !== null ? data.arrival_after_dispatch_minutes + ' {{ __('min') }}' : '—'"></p>
                </div>
            </div>

            <p class="text-sm text-luxury-muted" x-text="data.message"></p>
        </div>
    </template>

    <x-dispatch-map id="dispatch-map-{{ $booking->id }}" class="h-64 sm:h-80" x-show="scenario !== 3 && scenario !== 4" x-cloak />
</div>

<script>
    function dispatchCard(initial, pollUrl) {
        const justNowLabel = @json(__('Updated just now'));
        const secondsAgoLabel = @json(__('Updated :s sec ago'));
        const minutesAgoLabel = @json(__('Updated :m min ago'));

        return {
            data: initial,
            pollUrl,
            timer: null,
            clockTimer: null,
            lastUpdatedAt: Date.now(),
            lastUpdatedLabel: justNowLabel,
            pollFailed: false,
            consecutiveFailures: 0,

            get scenario() {
                return this.data ? this.data.scenario : null;
            },

            get status() {
                return this.data ? this.data.driver_status : null;
            },

            init() {
                this.renderMap();

                if (this.pollUrl) {
                    this.timer = setInterval(() => this.poll(), 15000);
                    // Ticks the "Updated Xs ago" label between polls, without
                    // waiting on a network round-trip — so the number is
                    // always honest even if a poll is late or fails.
                    this.clockTimer = setInterval(() => this.updateLabel(), 5000);
                }
            },

            renderMap() {
                if (! this.data || this.scenario === 3 || ! this.data.map) return;

                window.renderDispatchMap('dispatch-map-{{ $booking->id }}', {
                    office: this.data.map.office,
                    driver: this.data.map.driver,
                    pickup: this.data.map.pickup,
                    showRoute: this.data.map.show_route,
                    routeFrom: this.data.map.route_from,
                });
            },

            updateLabel() {
                const seconds = Math.round((Date.now() - this.lastUpdatedAt) / 1000);

                if (seconds < 10) {
                    this.lastUpdatedLabel = justNowLabel;
                } else if (seconds < 60) {
                    this.lastUpdatedLabel = secondsAgoLabel.replace(':s', seconds);
                } else {
                    this.lastUpdatedLabel = minutesAgoLabel.replace(':m', Math.round(seconds / 60));
                }
            },

            async poll() {
                try {
                    const response = await fetch(this.pollUrl, { headers: { 'Accept': 'application/json' } });
                    if (! response.ok) throw new Error('bad response');

                    const json = await response.json();
                    this.data = json.dispatch;
                    this.renderMap();

                    this.pollFailed = false;
                    this.consecutiveFailures = 0;
                    this.lastUpdatedAt = Date.now();
                    this.updateLabel();
                } catch (e) {
                    // A single missed tick isn't worth alarming the customer
                    // over — the next poll usually recovers silently. Only
                    // surface "connection interrupted" once it's happened
                    // twice in a row.
                    this.consecutiveFailures++;
                    if (this.consecutiveFailures >= 2) {
                        this.pollFailed = true;
                    }
                }
            },
        };
    }
</script>
