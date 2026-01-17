<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recollectie</title>
    <meta name="description" content="Een fijne plek voor je ideetjes">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✨</text></svg>">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <header class="header">
            <a href="{{ route('ideas.index') }}" class="logo">Recollectie</a>
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
