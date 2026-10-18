<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\PushNotificationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingController extends Controller
{
    public function edit(): View
    {
        $settings = NotificationSetting::query()
            ->get()
            ->sortBy(fn ($setting) => array_search($setting->event_type, array_keys(NotificationSetting::EVENTS)));

        $pushSettings = PushNotificationSetting::current();

        return view('admin.notification-settings.edit', compact('settings', 'pushSettings'));
    }

    public function update(Request $request): RedirectResponse
    {
        foreach (NotificationSetting::EVENTS as $eventType => $label) {
            $setting = NotificationSetting::where('event_type', $eventType)->first();

            if (! $setting) {
                continue;
            }

            $setting->update([
                'email_enabled' => $request->boolean("settings.{$eventType}.email_enabled"),
                'admin_panel_enabled' => $request->boolean("settings.{$eventType}.admin_panel_enabled"),
                // sms_enabled / push_enabled are intentionally left untouched —
                // no provider is wired up yet, so the UI keeps those locked off.
            ]);
        }

        return redirect()
            ->route('admin.notification-settings.edit')
            ->with('status', 'Notification preferences updated successfully.');
    }
}
