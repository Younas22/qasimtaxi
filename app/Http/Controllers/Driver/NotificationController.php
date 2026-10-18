<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::guard('driver')->user()->notifications()->paginate(20);

        return view('driver.notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::guard('driver')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('driver.notifications.index'));
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::guard('driver')->user()->unreadNotifications->markAsRead();

        return back(fallback: route('driver.notifications.index'))->with('status', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Auth::guard('driver')->user()->notifications()->findOrFail($id)->delete();

        return back(fallback: route('driver.notifications.index'))->with('status', 'Notification removed.');
    }
}
