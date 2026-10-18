/**
 * LimoSchedule browser push service worker.
 *
 * Deliberately generic — every piece of content (title, body, icon, badge,
 * url, type, booking_id) comes from the push payload itself, built
 * server-side by App\Services\PushNotificationService for every send. This
 * file never hardcodes a URL, an asset path, or branding, so an admin
 * changing the site logo or a route changing structure needs zero changes
 * here.
 *
 * Must be served from the site root (not /build/ or a subpath) — a service
 * worker's scope defaults to its own directory, and this app has pages
 * under /admin, /account (customer), and /driver that all need it.
 */

self.addEventListener('install', () => {
    // Activate this version immediately rather than waiting for every open
    // tab to close first — a push subscriber expects updates to take
    // effect right away, not after their next full page reload cycle.
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    let payload;

    try {
        payload = event.data.json();
    } catch (e) {
        // A non-JSON push body is treated as plain text rather than
        // dropped silently — still better than no notification at all.
        payload = { title: 'LimoSchedule', body: event.data.text() };
    }

    const title = payload.title || 'LimoSchedule';

    event.waitUntil(
        (async () => {
            // The Notification API has no "custom sound" option — browsers
            // dropped that years ago, so a fully-closed browser can only
            // ever play its own default ding for a real system push. What
            // IS controllable: any tab of the app that's currently open
            // (foreground or background) gets told to play the admin's
            // uploaded sound itself via <audio>, from a payload.sound URL
            // built fresh by PushNotificationService on every send — never
            // hardcoded here, so a re-upload takes effect instantly with no
            // change to this file.
            const clientList = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });

            if (payload.sound) {
                clientList.forEach((client) => client.postMessage({ type: 'play-notification-sound', url: payload.sound }));
            }

            // Suppress the OS's own default ding only when the user is
            // actively looking at an open tab (focused, not just "visible")
            // that is about to play the custom sound instead. A locked
            // phone, a background tab or a closed browser always gets the
            // OS sound + vibration — the custom <audio> can't play there,
            // and an autoplay-blocked tab must not leave the alert silent.
            const hasFocusedClient = clientList.some((client) => client.visibilityState === 'visible' && client.focused);

            return self.registration.showNotification(title, {
                body: payload.body || '',
                icon: payload.icon || undefined,
                badge: payload.badge || payload.icon || undefined,
                silent: Boolean(payload.sound) && hasFocusedClient,
                // Android: buzz the phone even when it's locked/face-down.
                vibrate: [400, 200, 400, 200, 400],
                // Keep the alert on screen (desktop) until it's dealt with,
                // instead of auto-hiding after a few seconds.
                requireInteraction: true,
                // A unique tag + renotify makes every push alert audibly,
                // even if an earlier notification for the same booking is
                // still sitting in the tray.
                tag: `${payload.type || 'push'}-${payload.booking_id || ''}-${Date.now()}`,
                renotify: true,
                timestamp: Date.now(),
                data: {
                    url: payload.url || '/',
                    type: payload.type || null,
                    booking_id: payload.booking_id || null,
                    ...(payload.data || {}),
                },
            });
        })()
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Focus an already-open LimoSchedule tab if one exists, on
            // whatever page it's currently on, then navigate it — avoids
            // piling up duplicate tabs every time a notification is
            // clicked, and works whether that tab is on the target page,
            // a different page, or idle in the background.
            for (const client of clientList) {
                if ('focus' in client) {
                    return client.focus().then(() => {
                        if ('navigate' in client) {
                            return client.navigate(targetUrl);
                        }
                    });
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
            }
        })
    );
});
