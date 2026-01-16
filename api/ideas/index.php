<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

$method = getMethod();
$auth = requireAuth();
$db = getDB();

if ($method === 'GET') {
    // Get all ideas for user
    $stmt = $db->prepare('
        SELECT i.id, i.content, i.created_at,
               GROUP_CONCAT(it.tag_id) as tag_ids
        FROM ideas i
        LEFT JOIN idea_tags it ON i.id = it.idea_id
        WHERE i.user_id = ?
        GROUP BY i.id
        ORDER BY i.created_at DESC
    ');
    $stmt->execute([$auth['userId']]);
    $ideas = $stmt->fetchAll();

    $result = array_map(function($idea) {
        return [
            'id' => $idea['id'],
            'content' => $idea['content'],
            'tagIds' => $idea['tag_ids'] ? explode(',', $idea['tag_ids']) : [],
            'createdAt' => strtotime($idea['created_at']) * 1000
        ];
    }, $ideas);

    jsonResponse(['ideas' => $result]);

} elseif ($method === 'POST') {
    // Create new idea
    $body = getJsonBody();
    $content = trim($body['content'] ?? '');
    $tagIds = $body['tagIds'] ?? [];

    if (!$content) {
        errorResponse('Inhoud is verplicht');
    }

    $id = generateUUID();

    $stmt = $db->prepare('INSERT INTO ideas (id, user_id, content) VALUES (?, ?, ?)');
    $stmt->execute([$id, $auth['userId'], $content]);

    // Add tags (filter out null/empty values)
    $tagIds = array_filter($tagIds, fn($tid) => !empty($tid));
    foreach ($tagIds as $tagId) {
        $stmt = $db->prepare('INSERT INTO idea_tags (idea_id, tag_id) VALUES (?, ?)');
        $stmt->execute([$id, $tagId]);
    }

    jsonResponse([
        'idea' => [
            'id' => $id,
            'content' => $content,
            'tagIds' => array_values($tagIds),
            'createdAt' => time() * 1000
        ]
    ]);

} else {
    errorResponse('Method not allowed', 405);
}
