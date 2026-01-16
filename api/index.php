<?php
// API Front Controller - routes all API requests

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Debug mode - remove in production
if (isset($_GET['debug'])) {
    header('Content-Type: application/json');
    echo json_encode(['uri' => $uri, 'method' => $method]);
    exit;
}

// Remove query string
$uri = strtok($uri, '?');

// Remove /api prefix and trailing slashes
$path = preg_replace('#^/api/?#', '', $uri);
$path = rtrim($path, '/');

// Route to appropriate handler
switch (true) {
    // Auth routes
    case $path === 'auth/register':
        require __DIR__ . '/auth/register.php';
        break;
    case $path === 'auth/login':
        require __DIR__ . '/auth/login.php';
        break;
    case $path === 'auth/logout':
        require __DIR__ . '/auth/logout.php';
        break;
    case $path === 'auth/me':
        require __DIR__ . '/auth/me.php';
        break;
    case $path === 'auth/profile':
        require __DIR__ . '/auth/profile.php';
        break;
    case $path === 'auth/password':
        require __DIR__ . '/auth/password.php';
        break;

    // Ideas routes
    case $path === 'ideas':
        require __DIR__ . '/ideas/index.php';
        break;
    case preg_match('#^ideas/([a-zA-Z0-9-]+)$#', $path, $matches):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/ideas/item.php';
        break;

    // Tags routes
    case $path === 'tags':
        require __DIR__ . '/tags/index.php';
        break;
    case preg_match('#^tags/([a-zA-Z0-9-]+)$#', $path, $matches):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/tags/item.php';
        break;

    // Test route
    case $path === 'test':
        require __DIR__ . '/test.php';
        break;

    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'API endpoint not found', 'path' => $path]);
}
