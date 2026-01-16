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
$stmt = $db->prepare('SELECT id FROM ideas WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $auth['userId']]);
if (!$stmt->fetch()) {
    errorResponse('Idee niet gevonden', 404);
}

if ($method === 'PUT') {
    $body = getJsonBody();
    $content = $body['content'] ?? null;
    $tagIds = $body['tagIds'] ?? null;

    // Update content if provided
    if ($content !== null) {
        $stmt = $db->prepare('UPDATE ideas SET content = ? WHERE id = ?');
        $stmt->execute([trim($content), $id]);
    }

    // Update tags if provided
    if ($tagIds !== null) {
        // Remove existing tags
        $stmt = $db->prepare('DELETE FROM idea_tags WHERE idea_id = ?');
        $stmt->execute([$id]);

        // Add new tags (filter out null/empty values)
        $tagIds = array_filter($tagIds, fn($tid) => !empty($tid));
        foreach ($tagIds as $tagId) {
            $stmt = $db->prepare('INSERT INTO idea_tags (idea_id, tag_id) VALUES (?, ?)');
            $stmt->execute([$id, $tagId]);
        }
    }

    jsonResponse(['success' => true]);

} elseif ($method === 'DELETE') {
    $stmt = $db->prepare('DELETE FROM ideas WHERE id = ?');
    $stmt->execute([$id]);

    jsonResponse(['success' => true]);

} else {
    errorResponse('Method not allowed', 405);
}
