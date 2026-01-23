<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ auth()->user()->theme ?? 'light' }}" data-color="{{ auth()->user()->color_theme ?? 'pink' }}">
@php
    $modules = config('modules');
    $currentModule = null;
    if (request()->routeIs('memorybox.*')) {
        $currentModule = 'memorybox';
    } elseif (request()->routeIs('checklist.*')) {
        $currentModule = 'checklist';
    } elseif (request()->routeIs('suggestions.*')) {
        $currentModule = 'suggestions';
    }
    $appName = __('common.app_name');
    $pageTitle = $currentModule ? $appName . ' - ' . __($modules[$currentModule]['name_key']) : $appName;
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
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
            ideas: @json(__('ideas')),
            todos: @json(__('todos')),
            suggestions: @json(__('suggestions')),
            password: @json(__('password')),
        };
        window.__locale = '{{ app()->getLocale() }}';
    </script>
</head>
<body>
    <div id="app">
        <header class="header">
            <a href="{{ route('home') }}" class="logo">{{ __('common.app_name') }}</a>

            @if($currentModule)
                <div class="app-switcher">
                    <button class="app-switcher-btn" onclick="this.nextElementSibling.classList.toggle('show')">
                        <span class="app-icon">{{ $modules[$currentModule]['icon'] }}</span>
                        <span class="app-name">{{ __($modules[$currentModule]['name_key']) }}</span>
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    <div class="app-dropdown">
                        @foreach($modules as $key => $module)
                            <a href="{{ route($module['route']) }}" class="app-dropdown-item {{ $key === $currentModule ? 'active' : '' }}">
                                <span class="app-dropdown-icon">{{ $module['icon'] }}</span>
                                <div class="app-dropdown-info">
                                    <span class="app-dropdown-name">{{ __($module['name_key']) }}</span>
                                    <span class="app-dropdown-desc">{{ __($module['description_key']) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="user-menu">
                <button class="user-btn" onclick="this.nextElementSibling.classList.toggle('show')">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="dropdown-arrow">▼</span>
                </button>
                <div class="menu-dropdown">
                    <a href="{{ route('profile') }}" class="menu-item">{{ __('common.profile') }}</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="menu-item">{{ __('common.admin') }}</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="menu-item logout">{{ __('common.logout') }}</button>
                    </form>
                </div>
            </div>
        </header>

        @if(request()->routeIs('profile') || request()->routeIs('admin.*'))
            <div class="floating-nav">
                <a href="{{ route('home') }}" class="floating-nav-btn" data-tooltip="Home">🏠</a>
                @foreach(config('modules') as $key => $module)
                    <a href="{{ route($module['route']) }}" class="floating-nav-btn" data-tooltip="{{ __($module['name_key']) }}">{{ $module['icon'] }}</a>
                @endforeach
            </div>
        @endif

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
        // Page transitions matching Vue component transitions
        (() => {
            // Fade in on page load
            requestAnimationFrame(() => {
                document.body.classList.add('page-loaded');
            });

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

                // Skip links inside forms (like logout)
                if (link.closest('form')) {
                    return;
                }

                // Skip if it's the same page (same href as current location)
                if (link.href === window.location.href) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();

                try {
                    const currentMain = document.querySelector('.main-content');
                    const currentHeader = document.querySelector('.header');
                    const currentFloatingNav = document.querySelector('.floating-nav');

                    // Start fetching immediately (parallel with exit animation)
                    const fetchPromise = fetch(link.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            return parser.parseFromString(html, 'text/html');
                        });

                    // Trigger exit animation on current content (runs parallel with fetch)
                    const forms = currentMain.querySelectorAll('[class*="form"], .idea-form, .suggestion-form, .add-list-form, .todo-header-box, .profile-form');
                    const lists = currentMain.querySelectorAll('.ideas-list, .filter-section, .empty-state, .suggestions-list, .todo-lists-grid, .todo-tasks');
                    const todoCards = currentMain.querySelectorAll('.todo-list-card, .todo-item-card');

                    // Homepage elements
                    const homePage = currentMain.querySelector('.home-page');

                    // Profile elements
                    const profilePage = currentMain.querySelector('.profile-page');

                    // Admin elements
                    const adminPage = currentMain.querySelector('.admin-page');
                    const adminHeader = currentMain.querySelector('.admin-header');
                    const adminLoader = currentMain.querySelector('.admin-loader');
                    const adminContent = currentMain.querySelector('.admin-content');
                    const adminCards = currentMain.querySelectorAll('.stat-card, .user-card, .suggestion-card');
                    const adminActions = currentMain.querySelector('.admin-actions');
                    const adminCard = currentMain.querySelector('.admin-card');

                    // Floating nav buttons
                    const floatingNavBtns = currentFloatingNav ? currentFloatingNav.querySelectorAll('.floating-nav-btn') : [];

                    // Add exit classes to trigger leave animations
                    forms.forEach(el => {
                        el.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        el.style.opacity = '0';
                        el.style.transform = 'scale(0.95)';
                    });

                    lists.forEach(el => {
                        el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(15px)';
                    });

                    // Animate todo cards with stagger
                    todoCards.forEach((el, index) => {
                        el.style.transition = 'all 0.25s ease';
                        el.style.transitionDelay = `${index * 0.03}s`;
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(-10px)';
                    });

                    // Animate entire homepage
                    if (homePage) {
                        homePage.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        homePage.style.opacity = '0';
                        homePage.style.transform = 'translateY(-20px)';
                    }

                    // Animate entire profile page
                    if (profilePage) {
                        profilePage.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        profilePage.style.opacity = '0';
                        profilePage.style.transform = 'translateY(-20px)';
                    }

                    // Animate admin page elements individually
                    if (adminPage) {
                        // Hide loader immediately if present
                        if (adminLoader) {
                            adminLoader.style.display = 'none';
                        }

                        // Make sure content is visible
                        if (adminContent) {
                            adminContent.classList.add('loaded');
                            adminContent.style.display = 'block';
                        }

                        // Now animate all child elements
                        const allAdminElements = adminPage.querySelectorAll('.admin-header, .admin-content, .admin-actions, .admin-card, .stat-card, .user-card, .suggestion-card');

                        // First set initial opacity to 1 to ensure they're visible
                        allAdminElements.forEach((el) => {
                            el.style.animation = 'none';
                            el.style.opacity = '1';
                            el.style.transform = 'translateY(0)';
                        });

                        // Force reflow
                        void adminPage.offsetHeight;

                        // Then in next frame, set transition and animate out
                        requestAnimationFrame(() => {
                            allAdminElements.forEach((el, index) => {
                                el.style.transition = 'all 0.25s ease';
                                el.style.transitionDelay = `${index * 0.02}s`;
                                el.style.opacity = '0';
                                el.style.transform = 'translateY(-15px)';
                            });
                        });
                    }

                    // Animate floating nav buttons out if present
                    floatingNavBtns.forEach((el, index) => {
                        el.style.transition = 'all 0.25s ease';
                        el.style.transitionDelay = `${index * 0.03}s`;
                        el.style.opacity = '0';
                        el.style.transform = 'scale(0.8) translateY(-5px)';
                    });

                    // Wait for both animation AND fetch to complete (whichever takes longer)
                    const [newDoc] = await Promise.all([
                        fetchPromise,
                        new Promise(resolve => setTimeout(resolve, 300))
                    ]);

                    const newMain = newDoc.querySelector('.main-content');
                    const newHeader = newDoc.querySelector('.header');
                    const newFloatingNav = newDoc.querySelector('.floating-nav');

                    if (!newMain) {
                        window.location.href = link.href;
                        return;
                    }

                    // Replace content
                    currentMain.innerHTML = newMain.innerHTML;
                    currentHeader.innerHTML = newHeader.innerHTML;

                    // Handle floating nav visibility based on target page
                    if (newFloatingNav && !currentFloatingNav) {
                        // Target page has floating nav but current doesn't - add it
                        const navContainer = document.querySelector('#app');
                        const mainContent = navContainer.querySelector('.main-content');
                        navContainer.insertBefore(newFloatingNav.cloneNode(true), mainContent);
                    } else if (!newFloatingNav && currentFloatingNav) {
                        // Target page doesn't have floating nav but current does - remove it
                        currentFloatingNav.remove();
                    } else if (newFloatingNav && currentFloatingNav) {
                        // Both have it - keep existing (it will animate in naturally via CSS)
                        // No action needed
                    }

                    // Execute inline scripts from the new page to define functions
                    const pageType = newDoc.body.className || 'default';
                    if (!window.executedScripts) window.executedScripts = new Set();

                    const scripts = Array.from(newDoc.querySelectorAll('script'));

                    scripts.forEach((script, index) => {
                        if (script.textContent && !script.src) {
                            const alwaysExecute = script.hasAttribute('data-always-execute');
                            const scriptId = `${pageType}-${index}`;

                            // Always execute scripts with data-always-execute, or scripts we haven't seen before
                            if (alwaysExecute || !window.executedScripts.has(scriptId)) {
                                try {
                                    const newScript = document.createElement('script');
                                    newScript.textContent = script.textContent;
                                    document.head.appendChild(newScript);
                                    document.head.removeChild(newScript);

                                    // Only track non-always-execute scripts
                                    if (!alwaysExecute) {
                                        window.executedScripts.add(scriptId);
                                    }
                                } catch (e) {
                                    console.warn('Script execution warning:', e);
                                }
                            }
                        }
                    });

                    // Update title and URL
                    document.title = newDoc.title;
                    document.documentElement.setAttribute('data-theme', newDoc.documentElement.getAttribute('data-theme'));
                    document.documentElement.setAttribute('data-color', newDoc.documentElement.getAttribute('data-color'));
                    window.history.pushState({}, '', link.href);

                    // Reinitialize Vue apps (this will trigger the enter animations naturally via `appear`)
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
