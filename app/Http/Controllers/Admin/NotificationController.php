<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $admin = Auth::guard('admin')->user();
        $notifications = $admin->notifications()->latest()->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::guard('admin')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('admin.notifications.index'));
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::guard('admin')->user()->unreadNotifications->markAsRead();

        return back(fallback: route('admin.notifications.index'))->with('status', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Auth::guard('admin')->user()->notifications()->findOrFail($id)->delete();

        return back(fallback: route('admin.notifications.index'))->with('status', 'Notification removed.');
    }
}
