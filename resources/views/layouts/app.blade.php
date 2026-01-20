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
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
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

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
