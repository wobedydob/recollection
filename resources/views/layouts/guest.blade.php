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
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
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
</body>
</html>
