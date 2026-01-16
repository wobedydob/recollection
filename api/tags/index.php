<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

$method = getMethod();
$auth = requireAuth();
$db = getDB();

if ($method === 'GET') {
    // Get all tags for user
    $stmt = $db->prepare('SELECT id, name, color, emoji FROM tags WHERE user_id = ? ORDER BY created_at ASC');
    $stmt->execute([$auth['userId']]);
    $tags = $stmt->fetchAll();

    $result = array_map(function($tag) {
        return [
            'id' => $tag['id'],
            'name' => $tag['name'],
            'color' => $tag['color'],
            'emoji' => $tag['emoji'] ?: null
        ];
    }, $tags);

    jsonResponse(['tags' => $result]);

} elseif ($method === 'POST') {
    // Create new tag
    $body = getJsonBody();
    $name = trim($body['name'] ?? '');
    $color = $body['color'] ?? '';
    $emoji = $body['emoji'] ?? null;

    if (!$name) {
        errorResponse('Naam is verplicht');
    }

    if (strlen($name) > 25) {
        errorResponse('Naam mag maximaal 25 tekens zijn');
    }

    if (!$color) {
        errorResponse('Kleur is verplicht');
    }

    $id = generateUUID();

    $stmt = $db->prepare('INSERT INTO tags (id, user_id, name, color, emoji) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$id, $auth['userId'], $name, $color, $emoji ?: null]);

    jsonResponse([
        'tag' => [
            'id' => $id,
            'name' => $name,
            'color' => $color,
            'emoji' => $emoji ?: null
        ]
    ]);

} else {
    errorResponse('Method not allowed', 405);
}
