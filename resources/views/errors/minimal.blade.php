<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ __('common.app_name') }}</title>
    <meta name="description" content="{{ __('common.app_description') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✨</text></svg>">
    @vite(['resources/scss/app.scss'])
    <script>
        // Apply saved theme immediately to prevent flash
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            const colorTheme = localStorage.getItem('color_theme') || 'pink';
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.setAttribute('data-color', colorTheme);
        })();
    </script>
</head>
<body>
    <div class="error-page">
        <div class="error-card">
            <a href="/" class="error-logo">{{ __('common.app_name') }}</a>
            <div class="error-icon">@yield('icon', '😵')</div>
            <h1 class="error-code">@yield('code')</h1>
            <h2 class="error-title">@yield('title')</h2>
            <p class="error-message">@yield('message')</p>
            <a href="/" class="error-home-btn">{{ __('errors.back_home') }}</a>
        </div>
    </div>
</body>
</html>
