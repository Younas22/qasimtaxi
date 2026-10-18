<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingSettingController extends Controller
{
    public function edit(): View
    {
        $settings = BookingSetting::current();
        $whatsappTokens = \App\Services\WhatsAppBookingLinkGenerator::availableTokens();

        return view('admin.booking-settings.edit', compact('settings', 'whatsappTokens'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = BookingSetting::current();

        $data = $request->validate([
            'whatsapp_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]*$/'],
            'whatsapp_message_template' => ['nullable', 'string', 'max:2000'],
        ]);

        $manualBookingEnabled = $request->boolean('manual_booking_enabled');
        $websiteBookingEnabled = $request->boolean('website_booking_enabled');

        if ($manualBookingEnabled && $websiteBookingEnabled) {
            return back()->withInput()->with('error', 'Manual Booking and Website Booking cannot both be enabled at the same time. Please disable one first.');
        }

        $settings->update([
            'manual_booking_enabled' => $manualBookingEnabled,
            'website_booking_enabled' => $websiteBookingEnabled,
            'guest_booking_enabled' => $request->boolean('guest_booking_enabled'),
            'voice_search_enabled' => $request->boolean('voice_search_enabled'),
            'auto_confirmation_enabled' => $request->boolean('auto_confirmation_enabled'),
            'driver_auto_assignment_enabled' => $request->boolean('driver_auto_assignment_enabled'),
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'whatsapp_message_template' => ($data['whatsapp_message_template'] ?? null) ?: BookingSetting::DEFAULT_WHATSAPP_TEMPLATE,
        ]);

        return back()->with('status', 'Booking settings updated successfully.');
    }
}
