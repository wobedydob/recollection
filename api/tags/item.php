<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

$method = getMethod();
$auth = requireAuth();
$db = getDB();

// Get ID from query parameter
$id = $_GET['id'] ?? null;

if (!$id) {
    errorResponse('ID is verplicht');
}

// Verify ownership
$stmt = $db->prepare('SELECT id FROM tags WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $auth['userId']]);
if (!$stmt->fetch()) {
    errorResponse('Tag niet gevonden', 404);
}

if ($method === 'DELETE') {
    $stmt = $db->prepare('DELETE FROM tags WHERE id = ?');
    $stmt->execute([$id]);

    jsonResponse(['success' => true]);

} else {
    errorResponse('Method not allowed', 405);
}
