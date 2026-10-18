<x-customer.layouts.app :title="__('Security')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Security') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage your password, sessions, and account security.') }}</p>
    </div>

    <div class="max-w-2xl space-y-6">
        {{-- Change Password --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Change Password') }}</h3>
            <form method="POST" action="{{ route('customer.security.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <x-admin.input-label for="current_password" value="Current Password" />
                    <x-admin.text-input id="current_password" name="current_password" type="password" required autocomplete="current-password" />
                    <x-admin.input-error :messages="$errors->get('current_password')" />
                </div>

                <div>
                    <x-admin.input-label for="password" value="New Password" />
                    <x-admin.text-input id="password" name="password" type="password" required autocomplete="new-password" />
                    <x-admin.input-error :messages="$errors->get('password')" />
                </div>

                <div>
                    <x-admin.input-label for="password_confirmation" value="Confirm New Password" />
                    <x-admin.text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
                    <x-admin.input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <x-admin.button type="submit" variant="primary">{{ __('Update Password') }}</x-admin.button>
            </form>
        </div>

        {{-- Active Sessions --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Active Sessions') }}</h3>
                @if ($activeSessions->count() > 1)
                    <x-confirm-modal
                        action="{{ route('customer.security.sessions.revoke-others') }}"
                        title="{{ __('Log Out Other Sessions') }}"
                        message="{{ __('This signs you out on every other device. You will stay signed in here.') }}"
                        confirm-label="{{ __('Log Out Others') }}"
                        trigger-label="{{ __('Log out all other sessions') }}" />
                @endif
            </div>

            <div class="space-y-3">
                @foreach ($activeSessions as $session)
                    @php $isCurrent = $session->session_id === $currentSessionId; @endphp
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-luxury-border bg-luxury-graphite/40 p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                                <x-icon name="phone" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="flex items-center gap-2 text-sm font-medium text-luxury-white">
                                    {{ $session->device_label ?? __('Unknown Device') }}
                                    @if ($isCurrent)
                                        <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-400">{{ __('This Device') }}</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-luxury-muted">{{ $session->ip_address ?? __('Unknown IP') }} &middot; {{ __('Last active :time', ['time' => $session->created_at->diffForHumans()]) }}</p>
                            </div>
                        </div>
                        @unless ($isCurrent)
                            <form method="POST" action="{{ route('customer.security.sessions.revoke', $session) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="shrink-0 rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        @endunless
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Login History --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Login History') }}</h3>

            <div class="space-y-3">
                @forelse ($loginHistories as $history)
                    <div class="flex items-center gap-3 rounded-xl border border-luxury-border bg-luxury-graphite/40 p-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="check-circle" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-luxury-white">{{ $history->device_label ?? __('Unknown Device') }}</p>
                            <p class="mt-0.5 text-xs text-luxury-muted">{{ $history->ip_address ?? __('Unknown IP') }} &middot; {{ $history->created_at->format('M d, Y — h:i A') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-luxury-muted">{{ __('No login history yet.') }}</p>
                @endforelse
            </div>

            @if ($loginHistories->hasPages())
                <div class="mt-5 flex items-center justify-between text-sm text-luxury-muted">
                    <div>
                        @if ($loginHistories->onFirstPage())
                            <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                        @else
                            <a href="{{ $loginHistories->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                        @endif
                    </div>
                    <div>
                        @if ($loginHistories->hasMorePages())
                            <a href="{{ $loginHistories->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                        @else
                            <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Future-ready --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('More Security Options') }}</h3>
            <p class="mb-4 text-xs text-luxury-muted">{{ __('These features are architected and on the roadmap — not yet active.') }}</p>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="flex items-center justify-between rounded-xl border border-luxury-border bg-luxury-graphite/40 p-4 opacity-60">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-luxury-slate text-luxury-muted">
                            <x-icon name="shield" class="h-4 w-4" />
                        </span>
                        <p class="text-sm font-medium text-luxury-white">{{ __('Two-Factor Authentication') }}</p>
                    </div>
                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-[11px] font-medium text-luxury-muted">{{ __('Soon') }}</span>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-luxury-border bg-luxury-graphite/40 p-4 opacity-60">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-luxury-slate text-luxury-muted">
                            <x-icon name="link" class="h-4 w-4" />
                        </span>
                        <p class="text-sm font-medium text-luxury-white">{{ __('Social Login') }}</p>
                    </div>
                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-[11px] font-medium text-luxury-muted">{{ __('Soon') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-customer.layouts.app>
