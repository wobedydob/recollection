<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('common.app_name'))</title>
    <meta name="description" content="{{ __('common.app_description') }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/apple-touch-icon-167x167.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-precomposed.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Recollectie">
    <meta name="theme-color" content="#e879a9">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <script>
        window.__translations = {
            common: @json(__('common')),
            password: @json(__('password')),
        };
        window.__locale = '{{ app()->getLocale() }}';
    </script>
</head>
<body>
    <div id="app" class="guest-page">
        @yield('content')
    </div>

    @stack('scripts')

    <script>
        // Make guest pages visible immediately
        requestAnimationFrame(() => {
            document.body.classList.add('page-loaded');
        });

        // Page transitions for guest pages (login, register, etc.)
        (() => {
            // Intercept all internal navigation for smooth transitions
            document.addEventListener('click', async (e) => {
                const link = e.target.closest('a');

                // Skip if not a link
                if (!link) {
                    return;
                }

                // Skip external links
                if (link.target === '_blank' || !link.href || !link.href.startsWith(window.location.origin)) {
                    return;
                }

                // Skip if it's the same page
                if (link.href === window.location.href) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();

                try {
                    const currentCard = document.querySelector('.auth-card');

                    // Start fetching immediately (parallel with exit animation)
                    const fetchPromise = fetch(link.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            return parser.parseFromString(html, 'text/html');
                        });

                    // Trigger exit animation
                    if (currentCard) {
                        currentCard.style.transition = 'all 0.3s ease';
                        currentCard.style.opacity = '0';
                        currentCard.style.transform = 'scale(0.95) translateY(-20px)';
                    }

                    // Wait for both animation AND fetch to complete
                    const [newDoc] = await Promise.all([
                        fetchPromise,
                        new Promise(resolve => setTimeout(resolve, 300))
                    ]);

                    const newContent = newDoc.querySelector('#app');

                    if (!newContent) {
                        window.location.href = link.href;
                        return;
                    }

                    // Replace content
                    document.querySelector('#app').innerHTML = newContent.innerHTML;

                    // Update title and URL
                    document.title = newDoc.title;
                    window.history.pushState({}, '', link.href);

                    // Reinitialize Vue apps if needed
                    if (window.initializeVueApps) {
                        window.initializeVueApps();
                    }

                } catch (error) {
                    console.error('Transition failed:', error);
                    window.location.href = link.href;
                }
            });
        })();
    </script>
</body>
</html>
