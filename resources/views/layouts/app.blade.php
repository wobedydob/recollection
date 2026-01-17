<!DOCTYPE html>
<html lang="nl" data-theme="{{ auth()->user()->theme ?? 'light' }}" data-color="{{ auth()->user()->color_theme ?? 'pink' }}">
@php
    $modules = config('modules');
    $currentModule = null;
    if (request()->routeIs('memorybox.*')) {
        $currentModule = 'memorybox';
    } elseif (request()->routeIs('todo.*')) {
        $currentModule = 'todo';
    }
    $pageTitle = $currentModule ? 'Recollectie - ' . $modules[$currentModule]['name'] : 'Recollectie';
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="Een fijne plek voor je ideetjes">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✨</text></svg>">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <header class="header">
            <a href="{{ route('home') }}" class="logo">Recollectie</a>

            @if($currentModule)
                <div class="app-switcher">
                    <button class="app-switcher-btn" onclick="this.nextElementSibling.classList.toggle('show')">
                        <span class="app-icon">{{ $modules[$currentModule]['icon'] }}</span>
                        <span class="app-name">{{ $modules[$currentModule]['name'] }}</span>
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    <div class="app-dropdown">
                        @foreach($modules as $key => $module)
                            <a href="{{ route($module['route']) }}" class="app-dropdown-item {{ $key === $currentModule ? 'active' : '' }}">
                                <span class="app-dropdown-icon">{{ $module['icon'] }}</span>
                                <div class="app-dropdown-info">
                                    <span class="app-dropdown-name">{{ $module['name'] }}</span>
                                    <span class="app-dropdown-desc">{{ $module['description'] }}</span>
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
                    <a href="{{ route('profile') }}" class="menu-item">Profiel</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="menu-item logout">Uitloggen</button>
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
