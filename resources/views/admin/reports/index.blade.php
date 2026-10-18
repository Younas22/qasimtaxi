<x-admin.layouts.app :title="__('Reports')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Reports') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Revenue, bookings, fleet, and customer insights across your business.') }}</p>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Monthly Revenue') }}</h3>
            <p class="text-xs text-luxury-muted">{{ __('Paid bookings over the last 12 months.') }}</p>
            <div class="mt-4 h-64">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Booking Growth') }}</h3>
            <p class="text-xs text-luxury-muted">{{ __('Total bookings created over the last 12 months.') }}</p>
            <div class="mt-4 h-64">
                <canvas id="bookingGrowthChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Report links --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('admin.reports.revenue') }}">
            <x-admin.stat-card :label="__('Revenue Report')" :value="currency($summary['total_revenue'])" :trend="__('All-time paid revenue')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.72-3.44a1.5 1.5 0 011.342-.81h13.376a1.5 1.5 0 011.342.81l1.72 3.44M12 15.75a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" /></svg>
            </x-admin.stat-card>
        </a>

        <a href="{{ route('admin.reports.bookings') }}">
            <x-admin.stat-card :label="__('Bookings Report')" :value="number_format($summary['total_bookings'])" :trend="__('All-time bookings')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </x-admin.stat-card>
        </a>

        <a href="{{ route('admin.reports.vehicles') }}">
            <x-admin.stat-card :label="__('Vehicles Report')" :value="number_format($summary['active_vehicles'])" :trend="__('Active vehicles')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM6 17H4v-4l1.5-4.5A2 2 0 017.4 7h9.2a2 2 0 011.9 1.5L20 13v4h-2m-12 0h8m-8 0H4" /></svg>
            </x-admin.stat-card>
        </a>

        <a href="{{ route('admin.reports.drivers') }}">
            <x-admin.stat-card :label="__('Drivers Report')" :value="number_format($summary['active_drivers'])" :trend="__('Active drivers')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" /></svg>
            </x-admin.stat-card>
        </a>

        <a href="{{ route('admin.reports.customers') }}">
            <x-admin.stat-card :label="__('Customers Report')" :value="number_format($summary['total_customers'])" :trend="__('Total customers')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.36-1.86M9 20H4v-2a3 3 0 015.36-1.86m0 0a4 4 0 116.36 0M7 10a4 4 0 118 0 4 4 0 01-8 0z" /></svg>
            </x-admin.stat-card>
        </a>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const style = getComputedStyle(document.documentElement);
                const gold = style.getPropertyValue('--color-luxury-gold').trim() || '#c9a24b';
                const secondary = style.getPropertyValue('--color-luxury-secondary').trim() || '#9ca3af';
                const muted = '#7a7a7a';
                const gridColor = 'rgba(255,255,255,0.06)';

                const commonScales = {
                    x: { ticks: { color: muted }, grid: { display: false } },
                    y: { ticks: { color: muted }, grid: { color: gridColor }, beginAtZero: true },
                };

                new Chart(document.getElementById('monthlyRevenueChart'), {
                    type: 'bar',
                    data: {
                        labels: {{ Illuminate\Support\Js::from($revenueChart['labels']) }},
                        datasets: [{
                            label: '{{ __('Revenue') }}',
                            data: {{ Illuminate\Support\Js::from($revenueChart['values']) }},
                            backgroundColor: gold,
                            borderRadius: 6,
                            maxBarThickness: 28,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: commonScales,
                    },
                });

                new Chart(document.getElementById('bookingGrowthChart'), {
                    type: 'line',
                    data: {
                        labels: {{ Illuminate\Support\Js::from($bookingChart['labels']) }},
                        datasets: [{
                            label: '{{ __('Bookings') }}',
                            data: {{ Illuminate\Support\Js::from($bookingChart['values']) }},
                            borderColor: secondary,
                            backgroundColor: secondary,
                            tension: 0.35,
                            pointRadius: 3,
                            fill: false,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: commonScales,
                    },
                });
            });
        </script>
    @endpush
</x-admin.layouts.app>
