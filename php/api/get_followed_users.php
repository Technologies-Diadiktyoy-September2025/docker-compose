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
    // Get followed users with their public lists count
    $stmt = $pdo->prepare('
        SELECT u.id, u.username, u.first_name, u.last_name, u.created_at,
               COUNT(ul.id) as public_lists_count,
               uf.created_at as followed_at
        FROM user_follows uf
        INNER JOIN users u ON uf.following_id = u.id
        LEFT JOIN user_lists ul ON u.id = ul.user_id AND ul.is_public = 1
        WHERE uf.follower_id = :user_id
        GROUP BY u.id
        ORDER BY uf.created_at DESC
    ');
    
    $stmt->execute([':user_id' => $user_id]);
    $followed_users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'followed_users' => $followed_users
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
