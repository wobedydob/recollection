<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

if (getMethod() !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$auth = requireAuth();
$body = getJsonBody();

$db = getDB();

$updates = [];
$params = [];

if (isset($body['name'])) {
    $updates[] = 'name = ?';
    $params[] = trim($body['name']);
}

if (isset($body['avatar'])) {
    $updates[] = 'avatar = ?';
    $params[] = $body['avatar'];
}

if (empty($updates)) {
    errorResponse('Niets om bij te werken');
}

$params[] = $auth['userId'];

$sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
$stmt = $db->prepare($sql);
$stmt->execute($params);

jsonResponse(['success' => true]);
