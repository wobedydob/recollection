<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

if (getMethod() !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$auth = getAuthUser();

if (!$auth) {
    jsonResponse(['user' => null]);
}

$db = getDB();

$stmt = $db->prepare('SELECT id, email, name, avatar, created_at FROM users WHERE id = ?');
$stmt->execute([$auth['userId']]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(['user' => null]);
}

jsonResponse([
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'avatar' => $user['avatar'],
        'createdAt' => strtotime($user['created_at']) * 1000
    ]
]);
