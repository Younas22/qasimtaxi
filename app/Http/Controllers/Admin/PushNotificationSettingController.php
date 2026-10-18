<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotificationSetting;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Admin Panel → Settings → Notifications → Browser Push Notifications —
 * the master/role/event-type control center. See PushNotificationSetting
 * for the column list and PushNotificationService for how every one of
 * these booleans is actually enforced.
 */
class PushNotificationSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'notification_sound' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:1024'],
        ]);

        $columns = array_merge(
            ['push_notifications_enabled', 'push_admin_enabled', 'push_customer_enabled', 'push_driver_enabled'],
            array_keys(PushNotificationSetting::ADMIN_EVENTS),
            array_keys(PushNotificationSetting::DRIVER_EVENTS),
            array_keys(PushNotificationSetting::CUSTOMER_EVENTS),
        );

        $data = [];

        foreach ($columns as $column) {
            $data[$column] = $request->boolean($column);
        }

        $settings = PushNotificationSetting::current();

        if ($request->hasFile('notification_sound')) {
            $data['notification_sound'] = $this->storeSound($request->file('notification_sound'), $settings->notification_sound);
        } elseif ($request->boolean('remove_notification_sound')) {
            $this->deleteSound($settings->notification_sound);
            $data['notification_sound'] = null;
        }

        $settings->update($data);

        return redirect()
            ->route('admin.notification-settings.edit')
            ->with('status', __('Browser push notification settings saved.'));
    }

    private function storeSound($file, ?string $previousFilename): string
    {
        $directory = public_path('uploads/push-sounds');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'notification-sound-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        $this->deleteSound($previousFilename);

        return $filename;
    }

    private function deleteSound(?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('uploads/push-sounds/'.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }

    public function sendTest(PushNotificationService $pushNotifications): RedirectResponse
    {
        $result = $pushNotifications->sendTest(Auth::guard('admin')->user());

        return redirect()
            ->route('admin.notification-settings.edit')
            ->with($result['sent'] ? 'status' : 'error', $result['message']);
    }
}
