{{-- Slim top-of-viewport progress bar giving instant "app-like" feedback
     on page navigation, since this is a traditional multi-page app with
     full page loads rather than client-side routing. --}}
<div id="page-progress-bar"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const bar = document.getElementById('page-progress-bar');
        if (!bar) return;

        let hideTimer;

        function start() {
            clearTimeout(hideTimer);
            bar.style.transition = 'none';
            bar.style.width = '0%';
            bar.classList.add('is-active');
            requestAnimationFrame(() => {
                bar.style.transition = '';
                bar.style.width = '70%';
            });
            hideTimer = setTimeout(finish, 4000);
        }

        function finish() {
            bar.style.width = '100%';
            setTimeout(() => {
                bar.classList.remove('is-active');
                setTimeout(() => { bar.style.width = '0%'; }, 200);
            }, 150);
        }

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (!link || !link.href) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            const href = link.getAttribute('href') || '';
            if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
            if (link.origin !== window.location.origin) return;

            start();
        });

        document.addEventListener('submit', (event) => {
            if (event.target instanceof HTMLFormElement && !event.defaultPrevented) {
                start();
            }
        });

        // Covers normal loads and back/forward-cache restores alike.
        window.addEventListener('pageshow', finish);
    });
</script>
