<x-admin.layouts.app :title="__('Email Settings')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Email Settings') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Configure outgoing mail via Resend, and verify delivery with a test email.') }}</p>
    </div>

    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.email-settings.update') }}" x-data="{ mailer: {{ \Illuminate\Support\Js::from(old('mailer', $settings->mailer)) }}, reveal: false }">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="text-sm font-semibold text-luxury-white">{{ __('Mail Driver') }}</h3>

                    <div>
                        <x-admin.input-label for="mailer" value="{{ __('Mailer') }}" />
                        <select id="mailer" name="mailer" x-model="mailer" required
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition sm:max-w-xs">
                            @foreach (\App\Models\EmailSetting::MAILERS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('MAIL_MAILER — which transport is used to send outgoing mail.') }}</p>
                        <x-admin.input-error :messages="$errors->get('mailer')" />
                    </div>
                </div>

                {{-- Resend Credentials --}}
                <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="mailer === 'resend'" x-cloak>
                    <div>
                        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Resend API Key') }}</h3>
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('RESEND_API_KEY — required while Resend is the selected mailer. Stored encrypted at rest.') }}</p>
                    </div>

                    <div>
                        <x-admin.input-label for="resend_api_key" value="{{ __('API Key') }}" />
                        <div class="flex items-center gap-2">
                            <input :type="reveal ? 'text' : 'password'" id="resend_api_key" name="resend_api_key" autocomplete="new-password"
                                placeholder="{{ $settings->resend_api_key ? __('•••••••••••••••• (unchanged)') : 're_...' }}"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            <button type="button" @click="reveal = !reveal" class="shrink-0 rounded-lg border border-luxury-border px-3 py-3 text-xs text-luxury-muted hover:text-luxury-gold">
                                <span x-text="reveal ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Leave blank to keep the currently saved key.') }}</p>
                        <x-admin.input-error :messages="$errors->get('resend_api_key')" />
                    </div>
                </div>

                {{-- From Address --}}
                <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <h3 class="text-sm font-semibold text-luxury-white">{{ __('From Address') }}</h3>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-admin.input-label for="from_address" value="{{ __('From Address') }}" />
                            <x-admin.text-input id="from_address" name="from_address" type="email" placeholder="bookings@yourdomain.com" value="{{ old('from_address', $settings->from_address) }}" />
                            <p class="mt-1 text-xs text-luxury-muted">MAIL_FROM_ADDRESS</p>
                            <x-admin.input-error :messages="$errors->get('from_address')" />
                        </div>

                        <div>
                            <x-admin.input-label for="from_name" value="{{ __('From Name') }}" />
                            <x-admin.text-input id="from_name" name="from_name" type="text" placeholder="{{ setting('company_name', config('app.name')) }}" value="{{ old('from_name', $settings->from_name) }}" />
                            <p class="mt-1 text-xs text-luxury-muted">MAIL_FROM_NAME</p>
                            <x-admin.input-error :messages="$errors->get('from_name')" />
                        </div>
                    </div>
                </div>

                {{-- Admin Notification Inbox --}}
                <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <div>
                        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Admin Notification Email') }}</h3>
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('New bookings, confirmations, cancellations, payments and every status change are emailed here. Separate multiple addresses with commas. Leave blank to email each admin at their login address instead.') }}</p>
                    </div>

                    <div>
                        <x-admin.input-label for="admin_notification_email" value="{{ __('Send Admin Notifications To') }}" />
                        <x-admin.text-input id="admin_notification_email" name="admin_notification_email" type="text" placeholder="owner@yourdomain.com, dispatch@yourdomain.com" value="{{ old('admin_notification_email', $settings->admin_notification_email) }}" />
                        <x-admin.input-error :messages="$errors->get('admin_notification_email')" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <x-admin.button type="submit" variant="primary">{{ __('Save Email Settings') }}</x-admin.button>
            </div>
        </form>

        {{-- Test Email --}}
        <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div>
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Send Test Email') }}</h3>
                <p class="mt-1 text-xs text-luxury-muted">{{ __('Send a sample message using the settings saved above to confirm delivery works end-to-end.') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.email-settings.test') }}" class="flex flex-col gap-3 sm:flex-row sm:items-start">
                @csrf
                <div class="flex-1">
                    <x-admin.text-input name="test_email" type="email" placeholder="you@example.com" required class="sm:max-w-sm" />
                    <x-admin.input-error :messages="$errors->get('test_email')" />
                </div>
                <x-admin.button type="submit" variant="secondary">{{ __('Send Test Email') }}</x-admin.button>
            </form>
        </div>
    </div>
</x-admin.layouts.app>
