<x-admin.layouts.app :title="__('Customers')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Customers') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage customer profiles, wallets, loyalty, and bookings.') }}</p>
        </div>

        <div class="flex items-center gap-2">
            @permission('reviews.view')
                <a href="{{ route('admin.reviews.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('Reviews') }}
                </a>
            @endpermission

            @permission('customers.create')
                <a href="{{ route('admin.customers.create') }}">
                    <x-admin.button type="button" variant="primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Add Customer') }}
                    </x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Contact') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Bookings') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Wallet') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Loyalty') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                                        @if ($customer->avatar_url)
                                            <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-xs font-semibold text-luxury-muted">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <span class="font-medium text-luxury-white hover:text-luxury-gold">{{ $customer->name }}</span>
                                </a>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">
                                <p>{{ $customer->email }}</p>
                                <p class="text-xs">{{ $customer->phone ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $customer->bookings_count }}</td>
                            <td class="px-6 py-3 text-luxury-gold">{{ currency($customer->wallet_balance) }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ number_format($customer->loyalty_points) }} pts</td>
                            <td class="px-6 py-3"><x-admin.status-badge :active="$customer->status" /></td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('customers.view')
                                        <a href="{{ route('admin.customers.show', $customer) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Profile') }}
                                        </a>
                                    @endpermission
                                    @permission('customers.edit')
                                        <a href="{{ route('admin.customers.edit', $customer) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                    @endpermission
                                    @permission('customers.delete')
                                        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('{{ __('Delete this customer?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-luxury-muted">{{ __('No customers added yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($customers as $customer)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                        @if ($customer->avatar_url)
                            <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-sm font-semibold text-luxury-muted">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $customer->name }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $customer->email }}</p>
                    </div>
                    <x-admin.status-badge :active="$customer->status" />
                </a>

                <div class="mt-3 grid grid-cols-3 gap-2 border-t border-luxury-border pt-3 text-center text-xs">
                    <div>
                        <p class="font-semibold text-luxury-white">{{ $customer->bookings_count }}</p>
                        <p class="text-luxury-muted">{{ __('Bookings') }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-luxury-gold">{{ currency($customer->wallet_balance) }}</p>
                        <p class="text-luxury-muted">{{ __('Wallet') }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-luxury-white">{{ number_format($customer->loyalty_points) }}</p>
                        <p class="text-luxury-muted">{{ __('Loyalty') }}</p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    @permission('customers.view')
                        <a href="{{ route('admin.customers.show', $customer) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Profile') }}
                        </a>
                    @endpermission
                    @permission('customers.edit')
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Edit') }}
                        </a>
                    @endpermission
                    @permission('customers.delete')
                        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('{{ __('Delete this customer?') }}');" class="ms-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tap-scale rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    @endpermission
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No customers added yet.') }}
            </div>
        @endforelse
    </div>
</x-admin.layouts.app>
