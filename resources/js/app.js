import './bootstrap';

/**
 * "Enable browser notifications on this device" Alpine component — shared by
 * the full settings card (components/push-notification-toggle.blade.php) and
 * the compact topbar badge (components/push-notification-badge.blade.php) on
 * the admin, customer, and driver sides, so there's exactly one copy of this
 * logic instead of one inline <script> per usage site. Lives here (rather
 * than inline in Blade) because both usages can render on the very same page
 * (e.g. a settings page also has the topbar), and Alpine resolves x-data
 * expressions against the global scope — hence the explicit `window.` assignment
 * rather than a plain module-scoped function declaration.
 *
 * Route URLs can't be resolved with Blade's route() helper here since this
 * file is a static, pre-built asset — callers pass them in instead.
 */
window.pushNotificationToggle = function (vapidPublicKey, serviceWorkerUrl, subscribeUrl, unsubscribeUrl, statusUrl, messages) {
    messages = messages || {};
    return {
        // states: 'checking' | 'unsupported' | 'denied' | 'default' | 'enabled' | 'error'
        state: 'checking',
        busy: false,
        errorMessage: '',
        subscription: null,

        async init() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                this.state = 'unsupported';
                return;
            }

            if (Notification.permission === 'denied') {
                this.state = 'denied';
                return;
            }

            try {
                const registration = await navigator.serviceWorker.register(serviceWorkerUrl);
                const existing = await registration.pushManager.getSubscription();

                // A push subscription lives at the browser/device level, not
                // the logged-in account level — on a shared computer, a
                // subscription created for one account is still sitting
                // right there in this browser's own storage after a
                // different account logs in. Trusting `existing` alone would
                // then show "Enabled" here even though the server has never
                // linked this endpoint to whoever is signed in right now.
                // Cross-checking the endpoint against push.status (scoped
                // server-side to the current session's account) is what
                // catches that instead of just hoping it's still accurate.
                if (existing && Notification.permission === 'granted' && await this.isRegisteredForCurrentAccount(existing.endpoint)) {
                    this.subscription = existing;
                    this.state = 'enabled';
                } else {
                    this.state = 'default';
                }
            } catch (e) {
                // Registration itself failing (blocked SW, non-secure
                // context, etc.) shouldn't break the rest of the page —
                // just fall back to the "enable" prompt.
                this.state = 'default';
            }
        },

        async isRegisteredForCurrentAccount(endpoint) {
            try {
                const response = await fetch(statusUrl + '?endpoint=' + encodeURIComponent(endpoint), {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) return false;

                const body = await response.json();

                return Boolean(body.subscribed);
            } catch (e) {
                // Can't confirm either way — default to "not enabled" so the
                // user sees an accurate Enable button rather than a false
                // "Enabled" they can't act on.
                return false;
            }
        },

        async enable() {
            if (this.busy) return;
            this.busy = true;
            this.errorMessage = '';

            try {
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    this.state = permission === 'denied' ? 'denied' : 'default';
                    return;
                }

                const registration = await navigator.serviceWorker.ready;

                let subscription = await registration.pushManager.getSubscription();

                if (!subscription) {
                    subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey),
                    });
                }

                await this.sendToServer(subscribeUrl, subscription.toJSON());

                this.subscription = subscription;
                this.state = 'enabled';
            } catch (e) {
                this.state = 'error';
                this.errorMessage = messages.enableError || 'Something went wrong enabling notifications. Please try again.';
            } finally {
                this.busy = false;
            }
        },

        async disable() {
            if (this.busy || !this.subscription) return;
            this.busy = true;

            try {
                const endpoint = this.subscription.endpoint;
                await this.subscription.unsubscribe();
                await this.sendToServer(unsubscribeUrl, { endpoint });
                this.subscription = null;
                this.state = 'default';
            } catch (e) {
                this.errorMessage = messages.disableError || 'Could not disable notifications. Please try again.';
                this.state = 'error';
            } finally {
                this.busy = false;
            }
        },

        async sendToServer(url, body) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify(body),
            });

            if (!response.ok) {
                throw new Error('Push subscription request failed');
            }

            return response.json();
        },

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }

            return outputArray;
        },
    };
};
