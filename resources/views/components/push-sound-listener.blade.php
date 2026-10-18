{{--
    Plays the admin-uploaded custom notification sound whenever this tab is
    open (foreground or background) and a push arrives — see public/sw.js,
    which posts {type:'play-notification-sound', url} to every open client.
    Included unconditionally in the admin/customer/driver layouts (not just
    the settings pages that render <x-push-notification-toggle>) since a
    push can arrive while the user is on any page of the panel.

    Browsers only allow programmatic audio playback after some user
    interaction has happened on the page (autoplay policy) — on a
    completely untouched tab the sound may be silently blocked; the
    notification banner itself still shows either way.
--}}
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type === 'play-notification-sound' && event.data.url) {
                new Audio(event.data.url).play().catch(() => {
                    // Blocked by the browser's autoplay policy, or the file
                    // failed to load — the notification banner already
                    // showed regardless, so this fails silently.
                });
            }
        });
    }
</script>
