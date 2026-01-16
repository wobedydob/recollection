<?php
// Simple JWT implementation

function base64UrlEncode($data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}

function createToken(array $payload): string {
    $header = ['typ' => 'JWT', 'alg' => 'HS256'];
    $payload['exp'] = time() + (7 * 24 * 60 * 60); // 7 days
    $payload['iat'] = time();

    $headerEncoded = base64UrlEncode(json_encode($header));
    $payloadEncoded = base64UrlEncode(json_encode($payload));

    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET, true);
    $signatureEncoded = base64UrlEncode($signature);

    return "$headerEncoded.$payloadEncoded.$signatureEncoded";
}

function verifyToken(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET, true);
    $expectedSignature = base64UrlEncode($signature);

    if (!hash_equals($expectedSignature, $signatureEncoded)) return null;

    $payload = json_decode(base64UrlDecode($payloadEncoded), true);

    if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) return null;

    return $payload;
}

function setAuthCookie(string $token) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    setcookie('auth_token', $token, [
        'expires' => time() + (7 * 24 * 60 * 60),
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function removeAuthCookie() {
    setcookie('auth_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function getAuthUser(): ?array {
    $token = $_COOKIE['auth_token'] ?? null;
    if (!$token) return null;
    return verifyToken($token);
}

function requireAuth(): array {
    $user = getAuthUser();
    if (!$user) {
        errorResponse('Niet ingelogd', 401);
    }
    return $user;
}

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function generateUUID(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
