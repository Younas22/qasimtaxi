# Browser Push Notifications

Direct Web Push (VAPID) — no OneSignal, Pusher, or Firebase. Covers Admin,
Customer, and Driver, with a master kill switch plus per-role and
per-event-type controls, all managed from **Admin Panel → Settings →
Notifications**.

## Architecture

```
Controller / existing Notification class
        ↓
PushNotificationService::send()
        ↓
master switch → role switch → event-type switch   (PushNotificationSetting)
        ↓
SendPushNotificationJob (queued)
        ↓
minishlink/web-push  →  browser's push service (FCM / WNS / Mozilla autopush)
        ↓
public/sw.js  →  🔔 notification  →  click opens the right page
```

Every push in the app goes through `App\Services\PushNotificationService`.
Nothing calls the Web Push library directly except
`App\Jobs\SendPushNotificationJob`.

Two ways a notification reaches that service:

1. **Existing Mail/database notifications opt in** — `App\Notifications\BookingNotification`
   (admin) and `App\Notifications\Customer\CustomerBookingNotification`
   (customer) each add `App\Channels\WebPushChannel::class` to their `via()`
   array and implement `toWebPush()`. This covers booking created/confirmed/
   cancelled and payment received for Admin and Customer, reusing the
   notification classes that already exist — no new call sites needed.
2. **Direct calls** for events with no existing Notification class —
   everything Driver-facing, plus a handful of Admin/Customer events
   (new customer, new driver, driver status, trip started/completed,
   booking cancelled-by-customer). See `PushNotificationService::send()`
   call sites in `BookingCreationService`, `Admin\BookingController`,
   `Customer\BookingController`, `Driver\RideController`,
   `Driver\StatusController`, `Admin\DriverController`,
   `Customer\Auth\RegisterController`.

### Why polymorphic subscriptions, not a `user_id` column

This app has three entirely separate guards/tables — `Admin`, `Customer`,
`Driver` (see `config/auth.php`) — no unified `users` table. `push_subscriptions`
uses `subscribable_type`/`subscribable_id` (Eloquent `morphTo`) instead, and
`pushSubscriptions()` is added to all three models via the
`App\Models\Concerns\HasPushSubscriptions` trait, matching the brief's
"add pushSubscriptions() to the User model" intent for this app's actual
shape.

### Why a dedicated settings table, not `NotificationSetting`

`notification_settings` (pre-existing) already had a `push_enabled` column
per event, but it's **one shared switch per event across every audience** —
no independent Admin/Customer/Driver control, and no Driver events at all.
`App\Models\PushNotificationSetting` is a new singleton table (same pattern
as `Setting`/`BookingSetting`) with the master switch, three role switches,
and one boolean per granular event type per role — see the migration for
the full column list.

## Files

| Concern | File |
|---|---|
| VAPID config | `config/webpush.php`, `.env` (`VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`) |
| Subscription storage | `database/migrations/..._create_push_subscriptions_table.php`, `App\Models\PushSubscription` |
| Settings storage | `database/migrations/..._create_push_notification_settings_table.php`, `App\Models\PushNotificationSetting` |
| Centralized permission + send logic | `App\Services\PushNotificationService` |
| Actual delivery (queued) | `App\Jobs\SendPushNotificationJob` |
| Laravel notification channel adapter | `App\Channels\WebPushChannel` |
| Subscribe/unsubscribe/status endpoints | `App\Http\Controllers\PushSubscriptionController`, routes in `routes/web.php` (`push.subscribe`, `push.unsubscribe`, `push.status`) |
| Service worker | `public/sw.js` |
| "Enable Notifications" UI | `resources/views/components/push-notification-toggle.blade.php` — used on the admin Notifications settings page, the customer Notifications page, and the driver Preferences page |
| Admin master/role/event settings UI | `resources/views/admin/notification-settings/edit.blade.php` (Browser Push Notifications section), `App\Http\Controllers\Admin\PushNotificationSettingController` |
| Dashboard status card | `App\Http\Controllers\Admin\DashboardController`, `resources/views/admin/dashboard.blade.php` |

## Setup (fresh install)

1. **Install the package** (already in `composer.json` if you're reading
   this from the repo — for a fresh setup elsewhere):
   ```bash
   composer require minishlink/web-push
   ```

2. **Generate VAPID keys**:
   ```bash
   php -r "require 'vendor/autoload.php'; print_r(Minishlink\WebPush\VAPID::createVapidKeys());"
   ```
   On some Windows/XAMPP-style local stacks this fails with
   `configuration file routines::no such file` — see the "Windows OpenSSL"
   note below.

3. **Add the keys to `.env`**:
   ```env
   VAPID_PUBLIC_KEY=...
   VAPID_PRIVATE_KEY=...
   VAPID_SUBJECT=mailto:admin@yourdomain.com
   ```

4. **Run migrations**:
   ```bash
   php artisan migrate
   ```

5. **Service worker** — already at `public/sw.js`; nothing to register
   manually, `push-notification-toggle.blade.php` registers it client-side
   the first time a user clicks "Enable Notifications".

6. **HTTPS** — required in production (`https://`); `http://localhost` is
   exempted by browsers as a secure-enough context for local development.

7. **Enable from Admin** — go to **Admin → Settings → Notifications**,
   turn on "Enable Browser Push Notifications" (master switch, OFF by
   default), confirm the role switches (Admin/Customer/Driver — all ON by
   default) and whichever event types you want, then **Save Notification
   Settings**.

8. **Test the Admin's own browser** — on that same page, click
   **Enable Notifications** in the "Browser Notifications" card at the top,
   allow the permission prompt, then click **Send Test Notification**.

9. **Test a Driver** — log in as a driver, go to **Preferences**, click
   **Enable Notifications**. Assign that driver to a booking from the
   admin panel and confirm a "New Booking Assigned" notification appears.

10. **Test a Customer** — log in as a customer, go to **Notifications**,
    click **Enable Notifications**. Trigger a booking confirmation,
    payment, or driver assignment and confirm the push arrives.

### Windows OpenSSL note

`minishlink/web-push` signs a fresh VAPID JWT (an EC/ECDSA operation) for
every push it sends. Some Windows PHP builds — this project's local XAMPP-
style stack included — fail EC key operations with
`error:07000072:configuration file routines::no such file`, because PHP's
OpenSSL extension has a compiled-in default `openssl.cnf` path
(`C:\Program Files\Common Files\SSL\openssl.cnf`) that doesn't exist on the
machine. `App\Providers\AppServiceProvider::configureWebPushOpenSsl()`
auto-detects a real `openssl.cnf` on this box and points `OPENSSL_CONF` at
it; `App\Jobs\SendPushNotificationJob` repeats the same check as a safety
net for queue workers. Linux production servers almost always ship a valid
default and need none of this — `config('webpush.openssl_conf')`
(`WEBPUSH_OPENSSL_CONF` in `.env`) is there only as an explicit override if
a production box ever needs one too.

## What's wired up vs. reserved for later

Every granular toggle in Admin → Settings → Notifications → Browser Push is
a real, saved setting and is enforced by `PushNotificationService` the
moment any code calls `send()` with that event type. Real trigger points
exist today for:

- **Admin**: New Booking, Booking Cancelled, Payment Received, New
  Customer, New Driver, Driver Status Update, Booking Status Update
  (booking confirmed).
- **Driver**: New Booking Assigned, Booking Cancelled.
- **Customer**: Booking Confirmed, Driver Assigned, Booking Cancelled,
  Payment Received, Trip Started, Trip Completed.

A few toggles exist and are fully functional but have no trigger point
yet, because the underlying lifecycle step doesn't exist anywhere else in
the app either (same "future-ready, not wired" precedent already
established by `notification_settings.sms_enabled`): Admin's "System
Alerts", Driver's "Booking Updated" / "Pickup Reminder" / "Customer
Update" / "Payment/Trip Update" / "Dispatch Update", and Customer's
"Booking Created" (the public booking flow redirects straight to the
confirmation page rather than sending a Notification), "Driver Accepted",
"Driver Arriving", and "Invoice Ready". Wiring any of these up later is
just one `PushNotificationService::send(...)` call at the right place —
the settings, permission chain, delivery, and UI are all already there.
