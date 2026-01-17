<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Recollectie</title>
    <meta name="description" content="Een fijne plek voor je ideetjes">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✨</text></svg>">
    @vite(['resources/scss/app.scss'])
</head>
<body>
    <div class="error-page">
        <div class="error-card">
            <a href="/" class="error-logo">Recollectie</a>
            <div class="error-icon">@yield('icon', '😵')</div>
            <h1 class="error-code">@yield('code')</h1>
            <h2 class="error-title">@yield('title')</h2>
            <p class="error-message">@yield('message')</p>
            <a href="/" class="error-home-btn">Terug naar home</a>
        </div>
    </div>
</body>
</html>
