@php
    $navItems = [
        // Overview
        ['label' => __('Dashboard'), 'route' => 'admin.dashboard', 'permission' => 'dashboard.view', 'icon' => 'grid'],
        ['label' => __('Reports'), 'route' => 'admin.reports.index', 'permission' => 'reports.view', 'icon' => 'bar-chart'],

        // Core operations
        [
            'type' => 'group',
            'label' => __('Bookings'),
            'permission' => 'bookings.view',
            'icon' => 'calendar',
            'children' => [
                ['label' => __('All Bookings'), 'route' => 'admin.bookings.index', 'permission' => 'bookings.view'],
                ['label' => __('Booking Settings'), 'route' => 'admin.booking-settings.edit', 'permission' => 'bookings.edit'],
            ],
        ],
        [
            'type' => 'group',
            'label' => __('Fleet'),
            'permission' => ['vehicles.view', 'drivers.view'],
            'icon' => 'car',
            'children' => [
                ['label' => __('All Vehicles'), 'route' => 'admin.vehicles.index', 'permission' => 'vehicles.view'],
                ['label' => __('Vehicle Categories'), 'route' => 'admin.vehicles.categories.index', 'permission' => 'vehicles.view'],
                ['label' => __('Drivers'), 'route' => 'admin.drivers.index', 'permission' => 'drivers.view'],
                ['label' => __('Live Fleet'), 'route' => 'admin.fleet.index', 'permission' => 'drivers.view'],
            ],
        ],
        [
            'type' => 'group',
            'label' => __('Customers'),
            'permission' => 'customers.view',
            'icon' => 'users',
            'children' => [
                ['label' => __('All Customers'), 'route' => 'admin.customers.index', 'permission' => 'customers.view'],
                ['label' => __('Reviews'), 'route' => 'admin.reviews.index', 'permission' => 'reviews.view'],
            ],
        ],

        // Money
        [
            'type' => 'group',
            'label' => __('Billing'),
            'permission' => ['pricing.view', 'payments.view', 'coupons.view', 'promotions.view'],
            'icon' => 'cash',
            'children' => [
                ['label' => __('Pricing'), 'route' => 'admin.pricing.index', 'permission' => 'pricing.view'],
                ['label' => __('Payments'), 'route' => 'admin.payment-gateways.index', 'permission' => 'payments.view'],
                ['label' => __('Coupons'), 'route' => 'admin.coupons.index', 'permission' => 'coupons.view'],
                ['label' => __('Promotions'), 'route' => 'admin.promotions.index', 'permission' => 'promotions.view'],
            ],
        ],

        // Content / website
        [
            'type' => 'group',
            'label' => __('Content'),
            'permission' => ['content.view', 'blog.view', 'routes.view', 'areas.view'],
            'icon' => 'document',
            'children' => [
                ['label' => __('Pages'), 'route' => 'admin.pages.index', 'permission' => 'content.view'],
                ['label' => __('Blog Posts'), 'route' => 'admin.blog.index', 'permission' => 'blog.view'],
                ['label' => __('Blog Categories'), 'route' => 'admin.blog.categories.index', 'permission' => 'blog.view'],
                ['label' => __('Blog Tags'), 'route' => 'admin.blog.tags.index', 'permission' => 'blog.view'],
                ['label' => __('Popular Routes'), 'route' => 'admin.popular-routes.index', 'permission' => 'routes.view'],
                ['label' => __('Route Types'), 'route' => 'admin.popular-routes.route-types.index', 'permission' => 'routes.view'],
                ['label' => __('Service Areas'), 'route' => 'admin.areas.index', 'permission' => 'areas.view'],
                ['label' => __('Redirects'), 'route' => 'admin.redirects.index', 'permission' => 'content.view'],
            ],
        ],
        [
            'type' => 'group',
            'label' => __('Locations'),
            'permission' => 'locations.view',
            'icon' => 'map',
            'children' => [
                ['label' => __('Countries'), 'route' => 'admin.locations.countries.index'],
                ['label' => __('States'), 'route' => 'admin.locations.states.index'],
                ['label' => __('Cities'), 'route' => 'admin.locations.cities.index'],
                ['label' => __('Airports'), 'route' => 'admin.locations.airports.index'],
                ['label' => __('Train Stations'), 'route' => 'admin.locations.train-stations.index'],
                ['label' => __('Pickup Points'), 'route' => 'admin.locations.pickup-points.index'],
            ],
        ],

        // Communication
        [
            'type' => 'group',
            'label' => __('Communication'),
            'permission' => ['messages.view', 'support.view'],
            'icon' => 'chat',
            'children' => [
                ['label' => __('Notifications'), 'route' => 'admin.notifications.index'],
                ['label' => __('Contact Messages'), 'route' => 'admin.contact-messages.index', 'permission' => 'messages.view'],
                ['label' => __('Support Tickets'), 'route' => 'admin.support-tickets.index', 'permission' => 'support.view'],
            ],
        ],

        // System / configuration
        [
            'type' => 'group',
            'label' => __('Settings'),
            'permission' => 'settings.view',
            'icon' => 'settings',
            'children' => [
                ['label' => __('General'), 'route' => 'admin.settings.edit', 'permission' => 'settings.view'],
                ['label' => __('Email'), 'route' => 'admin.email-settings.edit', 'permission' => 'settings.view'],
                ['label' => __('Notifications'), 'route' => 'admin.notification-settings.edit', 'permission' => 'settings.view'],
                ['label' => __('Integrations'), 'route' => 'admin.integrations.edit', 'permission' => 'system.edit'],
            ],
        ],
        [
            'type' => 'group',
            'label' => __('System'),
            'permission' => ['roles.view', 'languages.view', 'currencies.view', 'system.view'],
            'icon' => 'wrench',
            'children' => [
                ['label' => __('Roles & Permissions'), 'route' => 'admin.roles.index', 'permission' => 'roles.view'],
                ['label' => __('Languages'), 'route' => 'admin.languages.index', 'permission' => 'languages.view'],
                ['label' => __('Currencies'), 'route' => 'admin.currencies.index', 'permission' => 'currencies.view'],
                ['label' => __('System Tools'), 'route' => 'admin.system-tools.index', 'permission' => 'system.view'],
            ],
        ],
    ];

    $icons = [
        'grid' => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'car' => 'M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM6 17H4v-4l1.5-4.5A2 2 0 017.4 7h9.2a2 2 0 011.9 1.5L20 13v4h-2m-12 0h8m-8 0H4',
        'id' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4',
        'users' => 'M17 20h5v-2a3 3 0 00-5.36-1.86M9 20H4v-2a3 3 0 015.36-1.86m0 0a4 4 0 116.36 0M7 10a4 4 0 118 0 4 4 0 01-8 0z',
        'settings' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        'shield' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        'globe' => 'M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M11.5 3a17 17 0 00-2.5 9 17 17 0 002.5 9M12.5 3a17 17 0 012.5 9 17 17 0 01-2.5 9',
        'cash' => 'M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.72-3.44a1.5 1.5 0 011.342-.81h13.376a1.5 1.5 0 011.342.81l1.72 3.44M12 15.75a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z',
        'map' => 'M9 6.75L3 9.75v10.5l6-3m0-10.5l6 3m-6-3v13.5m6-10.5l6-3v10.5l-6 3m0-10.5v10.5m0 0l-6-3',
        'map-pin' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
        'route' => 'M4.5 19.5L19.5 4.5m0 0h-6m6 0v6M4.5 4.5l3 3m-3-3v6m0-6h6',
        'trending-up' => 'M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
        'credit-card' => 'M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25v-1.5a1.5 1.5 0 011.5-1.5h16.5a1.5 1.5 0 011.5 1.5v1.5M6 15.75h4.5',
        'bar-chart' => 'M3 13.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'wrench' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z',
        'bell' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
        'chat' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z',
        'document' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'pencil' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
        'sparkles' => 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z',
    ];

    $admin = auth()->guard('admin')->user();

    // A nav item's 'permission' can be a single slug or an array of slugs
    // (merged groups pulling children from several modules) — visible if
    // the admin has at least one of them, or if no permission is set at all.
    $canSeeNavItem = function ($permission) use ($admin) {
        if (! $permission) {
            return true;
        }

        return collect((array) $permission)->contains(fn ($slug) => $admin?->hasPermission($slug));
    };
@endphp

<div class="flex h-16 shrink-0 items-center gap-3 border-b border-luxury-border px-6">
    @if (setting('favicon_url'))
        <img src="{{ setting('favicon_url') }}" alt="{{ setting('company_name') }}" class="h-9 w-9 rounded-lg object-contain">
    @else
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-luxury-gold text-luxury-black font-bold">
            {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
        </div>
    @endif
    <div class="min-w-0 leading-tight">
        <p class="truncate text-sm font-semibold tracking-wide text-luxury-white">{{ setting('company_name', config('app.name', 'Limo Schedule')) }}</p>
        <p class="text-[11px] uppercase tracking-widest text-luxury-muted">{{ __('Admin Panel') }}</p>
    </div>
</div>

<nav class="scrollbar-luxury flex-1 space-y-1 overflow-y-auto px-3 py-6">
    @foreach ($navItems as $item)
        @continue(! $canSeeNavItem($item['permission'] ?? null))

        @if (($item['type'] ?? null) === 'group')
            @php
                $childIsActive = collect($item['children'])->contains(fn ($child) => $child['route'] && \Illuminate\Support\Facades\Route::has($child['route']) && request()->routeIs($child['route']));
            @endphp
            <div x-data="{ open: {{ $childIsActive ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                    class="flex w-full cursor-pointer items-center gap-3 rounded-lg border-s-2 px-4 py-2.5 text-sm font-medium transition {{ $childIsActive ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-transparent text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}" />
                    </svg>
                    {{ $item['label'] }}
                    <svg class="ms-auto h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition class="ms-4 mt-1 space-y-1 border-s border-luxury-border ps-4">
                    @foreach ($item['children'] as $child)
                        @continue(! $canSeeNavItem($child['permission'] ?? null))

                        @if ($child['route'] && \Illuminate\Support\Facades\Route::has($child['route']))
                            <a href="{{ route($child['route']) }}"
                                class="block rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs($child['route']) ? 'text-luxury-gold' : 'text-luxury-muted hover:text-luxury-white' }}">
                                {{ $child['label'] }}
                            </a>
                        @else
                            <span class="flex cursor-not-allowed items-center gap-2 rounded-lg px-3 py-2 text-sm text-luxury-muted/40">
                                {{ $child['label'] }}
                                <span class="ms-auto rounded-full bg-luxury-slate px-2 py-0.5 text-[10px] uppercase tracking-wide">{{ __('Soon') }}</span>
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @elseif ($item['route'] && \Illuminate\Support\Facades\Route::has($item['route']))
            <x-admin.nav-link :href="route($item['route'])" :active="request()->routeIs($item['route'].'*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}" />
                </svg>
                {{ $item['label'] }}
            </x-admin.nav-link>
        @else
            <span class="flex cursor-not-allowed items-center gap-3 rounded-lg border-s-2 border-transparent px-4 py-2.5 text-sm font-medium text-luxury-muted/40">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}" />
                </svg>
                {{ $item['label'] }}
                <span class="ms-auto rounded-full bg-luxury-slate px-2 py-0.5 text-[10px] uppercase tracking-wide">{{ __('Soon') }}</span>
            </span>
        @endif
    @endforeach
</nav>

<div class="border-t border-luxury-border p-4">
    <p class="text-center text-[11px] text-luxury-muted">&copy; {{ now()->year }} {{ setting('company_name', config('app.name')) }}</p>
</div>
