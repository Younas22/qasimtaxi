<x-admin.layouts.app :title="$customer->name">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                @if ($customer->avatar_url)
                    <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="h-full w-full object-cover">
                @else
                    <span class="text-xl font-semibold text-luxury-muted">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl font-semibold text-luxury-white">{{ $customer->name }}</h2>
                    <x-admin.status-badge :active="$customer->status" />
                </div>
                <p class="text-sm text-luxury-muted">{{ $customer->email }} &middot; {{ $customer->phone ?? '—' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-1.5 rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                {{ __('Back') }}
            </a>
            @permission('customers.edit')
                <a href="{{ route('admin.customers.edit', $customer) }}">
                    <x-admin.button type="button" variant="secondary">{{ __('Edit Profile') }}</x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    {{-- Summary stats --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-admin.stat-card label="{{ __('Bookings') }}" :value="$customer->bookings()->count()">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card label="{{ __('Wallet Balance') }}" :value="currency($customer->wallet_balance)">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.72-3.44a1.5 1.5 0 011.342-.81h13.376a1.5 1.5 0 011.342.81l1.72 3.44M12 15.75a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card label="{{ __('Loyalty Points') }}" :value="number_format($customer->loyalty_points)">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card label="{{ __('Reviews') }}" :value="$customer->reviews->count()">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
        </x-admin.stat-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Booking History --}}
            <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
                <div class="border-b border-luxury-border px-6 py-4">
                    <h3 class="text-sm font-semibold text-luxury-white">{{ __('Booking History') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead>
                            <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                                <th class="px-6 py-3 font-medium">{{ __('Booking #') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Vehicle') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Driver') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Pickup') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-end font-medium">{{ __('Fare') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-luxury-border/60">
                            @forelse ($bookings as $booking)
                                <tr class="hover:bg-luxury-graphite">
                                    <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                                    <td class="px-6 py-3 text-luxury-muted">{{ $booking->vehicle?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-luxury-muted">{{ $booking->driver?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-3">
                                        <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium capitalize text-luxury-gold">{{ $booking->status }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-end text-luxury-white">{{ currency($booking->fare_amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-luxury-muted">{{ __('No bookings yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Reviews --}}
            <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
                <div class="border-b border-luxury-border px-6 py-4">
                    <h3 class="text-sm font-semibold text-luxury-white">{{ __('Reviews') }}</h3>
                </div>
                <div class="divide-y divide-luxury-border/60">
                    @forelse ($customer->reviews as $review)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <x-rating-stars :rating="$review->rating" size="h-4 w-4" />
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide {{ $review->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400' : ($review->status === 'pending' ? 'bg-luxury-gold/10 text-luxury-gold' : 'bg-red-500/10 text-red-400') }}">
                                    {{ $review->status_label }}
                                </span>
                            </div>
                            @if ($review->comment)
                                <p class="mt-2 text-sm text-luxury-white">{{ $review->comment }}</p>
                            @endif
                            <p class="mt-1 text-xs text-luxury-muted">
                                {{ $review->driver?->name ?? __('Unknown driver') }} &middot; {{ $review->vehicle?->name ?? __('Unknown vehicle') }} &middot; {{ $review->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-luxury-muted">{{ __('No reviews yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar column --}}
        <div class="space-y-6">
            {{-- Saved Addresses --}}
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-data="{ open: false }">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-luxury-white">{{ __('Saved Addresses') }}</h3>
                    @permission('customers.edit')
                        <button type="button" @click="open = !open" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                            <span x-text="open ? 'Cancel' : '+ Add'"></span>
                        </button>
                    @endpermission
                </div>

                <div x-show="open" x-cloak x-transition class="mb-4">
                    <form method="POST" action="{{ route('admin.customers.addresses.store', $customer) }}" class="space-y-3">
                        @csrf
                        <input type="text" name="label" placeholder="{{ __('Label (e.g. Home)') }}" required
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <input type="text" name="address_line" placeholder="{{ __('Address') }}" required
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <select name="city_id" class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            <option value="">{{ __('No city') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                        <label class="flex items-center gap-2 text-xs text-luxury-muted">
                            <input type="checkbox" name="is_default" value="1" class="rounded border-luxury-border bg-luxury-graphite text-luxury-gold focus:ring-luxury-gold">
                            {{ __('Set as default') }}
                        </label>
                        <x-admin.button type="submit" variant="primary" class="w-full !py-2 text-xs">{{ __('Save Address') }}</x-admin.button>
                    </form>
                </div>

                <div class="space-y-3">
                    @forelse ($customer->addresses as $address)
                        <div class="rounded-lg border border-luxury-border/60 p-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wide text-luxury-white">{{ $address->label }}</span>
                                @if ($address->is_default)
                                    <span class="rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] text-luxury-gold">{{ __('Default') }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-luxury-muted">{{ $address->address_line }}{{ $address->city ? ', '.$address->city->name : '' }}</p>
                            @permission('customers.edit')
                                <div class="mt-2 flex items-center gap-2">
                                    @unless ($address->is_default)
                                        <form method="POST" action="{{ route('admin.addresses.default', $address) }}">
                                            @csrf
                                            <button type="submit" class="text-[11px] text-luxury-muted hover:text-luxury-gold">{{ __('Make default') }}</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('admin.addresses.destroy', $address) }}" onsubmit="return confirm('{{ __('Remove this address?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[11px] text-red-400 hover:text-red-300">{{ __('Remove') }}</button>
                                    </form>
                                </div>
                            @endpermission
                        </div>
                    @empty
                        <p class="text-xs text-luxury-muted">{{ __('No saved addresses.') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Wallet --}}
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-data="{ open: false }">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Wallet') }}</h3>
                        <p class="text-lg font-semibold text-luxury-gold">{{ currency($customer->wallet_balance) }}</p>
                    </div>
                    @permission('customers.edit')
                        <button type="button" @click="open = !open" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                            <span x-text="open ? 'Cancel' : '+ Adjust'"></span>
                        </button>
                    @endpermission
                </div>

                <div x-show="open" x-cloak x-transition class="mb-4">
                    <form method="POST" action="{{ route('admin.customers.wallet.store', $customer) }}" class="space-y-3">
                        @csrf
                        <select name="type" class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            <option value="credit">{{ __('Credit (add funds)') }}</option>
                            <option value="debit">{{ __('Debit (deduct funds)') }}</option>
                        </select>
                        <input type="number" name="amount" step="0.01" min="0.01" placeholder="{{ __('Amount') }}" required
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <input type="text" name="reason" placeholder="{{ __('Reason (optional)') }}"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <x-admin.button type="submit" variant="primary" class="w-full !py-2 text-xs">{{ __('Update Wallet') }}</x-admin.button>
                    </form>
                    <x-admin.input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                <div class="scrollbar-luxury max-h-56 space-y-2 overflow-y-auto">
                    @forelse ($customer->walletTransactions as $transaction)
                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <p class="text-luxury-white">{{ $transaction->reason ?? ucfirst($transaction->type) }}</p>
                                <p class="text-luxury-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <span class="font-medium {{ $transaction->type === 'credit' ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $transaction->type === 'credit' ? '+' : '-' }}{{ currency($transaction->amount) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-luxury-muted">{{ __('No wallet activity yet.') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Loyalty --}}
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-data="{ open: false }">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Loyalty Points') }}</h3>
                        <p class="text-lg font-semibold text-luxury-gold">{{ number_format($customer->loyalty_points) }} pts</p>
                    </div>
                    @permission('customers.edit')
                        <button type="button" @click="open = !open" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                            <span x-text="open ? 'Cancel' : '+ Adjust'"></span>
                        </button>
                    @endpermission
                </div>

                <div x-show="open" x-cloak x-transition class="mb-4">
                    <form method="POST" action="{{ route('admin.customers.loyalty.store', $customer) }}" class="space-y-3">
                        @csrf
                        <select name="type" class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            <option value="earned">{{ __('Earn (add points)') }}</option>
                            <option value="redeemed">{{ __('Redeem (deduct points)') }}</option>
                        </select>
                        <input type="number" name="points" min="1" placeholder="{{ __('Points') }}" required
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <input type="text" name="reason" placeholder="{{ __('Reason (optional)') }}"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <x-admin.button type="submit" variant="primary" class="w-full !py-2 text-xs">{{ __('Update Points') }}</x-admin.button>
                    </form>
                    <x-admin.input-error :messages="$errors->get('points')" class="mt-2" />
                </div>

                <div class="scrollbar-luxury max-h-56 space-y-2 overflow-y-auto">
                    @forelse ($customer->loyaltyTransactions as $transaction)
                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <p class="text-luxury-white">{{ $transaction->reason ?? ucfirst($transaction->type) }}</p>
                                <p class="text-luxury-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <span class="font-medium {{ $transaction->type === 'earned' ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $transaction->type === 'earned' ? '+' : '-' }}{{ number_format($transaction->points) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-luxury-muted">{{ __('No loyalty activity yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>
