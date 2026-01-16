<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

if (getMethod() !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$body = getJsonBody();
$email = trim($body['email'] ?? '');
$password = $body['password'] ?? '';

if (!$email || !$password) {
    errorResponse('E-mail en wachtwoord zijn verplicht');
}

$db = getDB();

$stmt = $db->prepare('SELECT id, email, password, name, avatar, created_at FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !verifyPassword($password, $user['password'])) {
    errorResponse('Ongeldige inloggegevens');
}

// Create token and set cookie
$token = createToken(['userId' => $user['id'], 'email' => $user['email']]);
setAuthCookie($token);

jsonResponse([
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'avatar' => $user['avatar'],
        'createdAt' => strtotime($user['created_at']) * 1000
    ]
]);
