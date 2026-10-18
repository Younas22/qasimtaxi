<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::guard('customer')->user()->notifications()->paginate(20);

        return view('customer.notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::guard('customer')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::guard('customer')->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $notification = Auth::guard('customer')->user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('status', 'Notification deleted.');
    }
}
