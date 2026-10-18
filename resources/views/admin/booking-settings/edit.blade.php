<x-admin.layouts.app :title="__('Booking Settings')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Booking Settings') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Control how and where bookings can be created, and automate confirmation and driver assignment.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.booking-settings.update') }}">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6"
                x-data="{
                    manualBooking: {{ old('manual_booking_enabled', $settings->manual_booking_enabled) ? 'true' : 'false' }},
                    websiteBooking: {{ old('website_booking_enabled', $settings->website_booking_enabled) ? 'true' : 'false' }},
                }">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Booking Channels') }}</h3>
                <p class="text-xs text-luxury-muted">{{ __('Manual Booking and Website Booking cannot both be active — enabling one turns the other off.') }}</p>

                <x-admin.toggle
                    name="manual_booking_enabled"
                    :checked="old('manual_booking_enabled', $settings->manual_booking_enabled)"
                    label="{{ __('Manual Booking') }}"
                    description="{{ __("Allow admins to create bookings directly from this admin panel. When disabled, the 'New Booking' action is blocked.") }}"
                    x-model="manualBooking"
                    @change="if (manualBooking) websiteBooking = false" />

                <x-admin.toggle
                    name="website_booking_enabled"
                    :checked="old('website_booking_enabled', $settings->website_booking_enabled)"
                    label="{{ __('Website Booking') }}"
                    description="{{ __('Allow customers to submit bookings from the public website / booking widget.') }}"
                    x-model="websiteBooking"
                    @change="if (websiteBooking) manualBooking = false" />

                <x-admin.toggle
                    name="guest_booking_enabled"
                    :checked="old('guest_booking_enabled', $settings->guest_booking_enabled)"
                    label="{{ __('Guest Booking') }}"
                    description="{{ __('Allow visitors to book without creating a customer account first. Requires Website Booking to be meaningful.') }}" />

                <x-admin.toggle
                    name="voice_search_enabled"
                    :checked="old('voice_search_enabled', $settings->voice_search_enabled)"
                    label="{{ __('Voice Search') }}"
                    description="{{ __("Show a microphone button on the booking widget. Voice input is not wired up yet — this only controls the button's visibility.") }}" />
            </div>

            <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Automation') }}</h3>

                <x-admin.toggle
                    name="auto_confirmation_enabled"
                    :checked="old('auto_confirmation_enabled', $settings->auto_confirmation_enabled)"
                    label="{{ __('Auto Confirmation') }}"
                    description="{{ __('Automatically confirm new bookings instead of leaving them Pending for manual review.') }}" />

                <x-admin.toggle
                    name="driver_auto_assignment_enabled"
                    :checked="old('driver_auto_assignment_enabled', $settings->driver_auto_assignment_enabled)"
                    label="{{ __('Driver Auto Assignment') }}"
                    description="{{ __('Automatically assign the next available online driver to a booking when no driver is chosen.') }}" />
            </div>

            {{-- WhatsApp Booking --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6"
                x-data="whatsappSettings({{ \Illuminate\Support\Js::from(old('whatsapp_message_template', $settings->whatsapp_message_template ?: \App\Models\BookingSetting::DEFAULT_WHATSAPP_TEMPLATE)) }})">
                <div>
                    <h3 class="text-sm font-semibold text-luxury-white">{{ __('WhatsApp Booking') }}</h3>
                    <p class="mt-1 text-xs text-luxury-muted">
                        {{ __('While Manual Booking is enabled, a "Book Now" action appears on bookings that opens WhatsApp with this message pre-filled from that booking\'s data.') }}
                    </p>
                </div>

                <div>
                    <x-admin.input-label for="whatsapp_number" value="{{ __('WhatsApp Number') }}" />
                    <x-admin.text-input id="whatsapp_number" name="whatsapp_number" type="text" placeholder="{{ __('e.g. +1 555 123 4567') }}"
                        value="{{ old('whatsapp_number', $settings->whatsapp_number) }}" class="sm:max-w-xs" />
                    <p class="mt-1 text-xs text-luxury-muted">{{ __('Include the country code. Formatting characters are stripped automatically.') }}</p>
                    <x-admin.input-error :messages="$errors->get('whatsapp_number')" />
                </div>

                <div>
                    <x-admin.input-label for="whatsapp_message_template" value="{{ __('Message Template') }}" />
                    <textarea id="whatsapp_message_template" name="whatsapp_message_template" rows="6" x-model="template"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 font-mono text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition"></textarea>
                    <x-admin.input-error :messages="$errors->get('whatsapp_message_template')" />
                </div>

                <div>
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ __('Insert a placeholder') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($whatsappTokens as $token => $description)
                            <button type="button" @click="insertToken('{{ $token }}')" title="{{ $description }}"
                                class="rounded-full border border-luxury-border px-2.5 py-1 font-mono text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                {{ $token }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ __('Live Preview') }}</p>
                    <p class="whitespace-pre-wrap text-sm text-luxury-white" x-text="preview()"></p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Save Settings') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>

<script>
    function whatsappSettings(initialTemplate) {
        return {
            template: initialTemplate,
            sample: {
                '{customer_name}': 'Sophia Bennett',
                '{booking_number}': 'BK-7F3K9QX2',
                '{booking_type}': 'Airport Transfer',
                '{pickup_location}': 'JFK International Airport',
                '{dropoff_location}': 'The Plaza Hotel, Manhattan',
                '{pickup_date}': 'Aug 12, 2026',
                '{pickup_time}': '06:30 PM',
                '{vehicle_name}': 'Mercedes S-Class',
                '{fare_amount}': '$185.00',
                '{status}': 'Confirmed',
                '{passengers}': '2',
            },
            insertToken(token) {
                const el = document.getElementById('whatsapp_message_template');
                const start = el.selectionStart ?? this.template.length;
                const end = el.selectionEnd ?? this.template.length;

                this.template = this.template.slice(0, start) + token + this.template.slice(end);

                this.$nextTick(() => {
                    el.focus();
                    el.selectionStart = el.selectionEnd = start + token.length;
                });
            },
            preview() {
                let text = this.template;
                for (const [token, value] of Object.entries(this.sample)) {
                    text = text.split(token).join(value);
                }
                return text;
            },
        };
    }
</script>
