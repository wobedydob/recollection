<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

if (getMethod() !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$auth = requireAuth();
$body = getJsonBody();

$currentPassword = $body['currentPassword'] ?? '';
$newPassword = $body['newPassword'] ?? '';

if (!$currentPassword || !$newPassword) {
    errorResponse('Huidig en nieuw wachtwoord zijn verplicht');
}

if (strlen($newPassword) < 6) {
    errorResponse('Nieuw wachtwoord moet minimaal 6 tekens zijn');
}

$db = getDB();

// Get current password hash
$stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
$stmt->execute([$auth['userId']]);
$user = $stmt->fetch();

if (!$user) {
    errorResponse('Gebruiker niet gevonden', 404);
}

// Verify current password
if (!verifyPassword($currentPassword, $user['password'])) {
    errorResponse('Huidig wachtwoord is onjuist');
}

// Update password
$newHash = hashPassword($newPassword);
$stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([$newHash, $auth['userId']]);

jsonResponse(['success' => true]);
