<?php
session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get user's lists
    $stmt = $pdo->prepare('
        SELECT ul.*, 
               COUNT(li.id) as item_count,
               u.username
        FROM user_lists ul
        LEFT JOIN list_items li ON ul.id = li.list_id
        LEFT JOIN users u ON ul.user_id = u.id
        WHERE ul.user_id = :user_id
        GROUP BY ul.id
        ORDER BY ul.updated_at DESC
    ');
    $stmt->execute([':user_id' => $user_id]);
    $user_lists = $stmt->fetchAll();

    // Get followed users' public lists
    $stmt = $pdo->prepare('
        SELECT ul.*, 
               COUNT(li.id) as item_count,
               u.username
        FROM user_lists ul
        LEFT JOIN list_items li ON ul.id = li.list_id
        LEFT JOIN users u ON ul.user_id = u.id
        INNER JOIN user_follows uf ON ul.user_id = uf.following_id
        WHERE uf.follower_id = :user_id AND ul.is_public = 1
        GROUP BY ul.id
        ORDER BY ul.updated_at DESC
    ');
    $stmt->execute([':user_id' => $user_id]);
    $followed_lists = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'lists' => $user_lists,
        'followed_lists' => $followed_lists
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
