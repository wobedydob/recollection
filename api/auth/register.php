<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

if (getMethod() !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$body = getJsonBody();
$email = trim($body['email'] ?? '');
$password = $body['password'] ?? '';
$name = trim($body['name'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse('Ongeldig e-mailadres');
}

if (strlen($password) < 6) {
    errorResponse('Wachtwoord moet minimaal 6 tekens zijn');
}

if (!$name) {
    errorResponse('Naam is verplicht');
}

$db = getDB();

// Check if email already exists
$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    errorResponse('E-mailadres is al in gebruik');
}

// Create user
$id = generateUUID();
$hashedPassword = hashPassword($password);

$stmt = $db->prepare('INSERT INTO users (id, email, password, name) VALUES (?, ?, ?, ?)');
$stmt->execute([$id, $email, $hashedPassword, $name]);

// Create token and set cookie
$token = createToken(['userId' => $id, 'email' => $email]);
setAuthCookie($token);

jsonResponse([
    'user' => [
        'id' => $id,
        'email' => $email,
        'name' => $name,
        'avatar' => null,
        'createdAt' => time() * 1000
    ]
]);
