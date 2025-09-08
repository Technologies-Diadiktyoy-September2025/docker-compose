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
$search_query = trim($_GET['q'] ?? '');

if (empty($search_query)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit;
}

try {
    // Search for users by username, first name, or last name
    $stmt = $pdo->prepare('
        SELECT u.id, u.username, u.first_name, u.last_name, u.created_at,
               COUNT(ul.id) as public_lists_count,
               CASE WHEN uf.id IS NOT NULL THEN 1 ELSE 0 END as is_following
        FROM users u
        LEFT JOIN user_lists ul ON u.id = ul.user_id AND ul.is_public = 1
        LEFT JOIN user_follows uf ON u.id = uf.following_id AND uf.follower_id = :user_id_join
        WHERE u.id != :user_id_filter 
        AND (u.username LIKE :search1 OR u.first_name LIKE :search2 OR u.last_name LIKE :search3)
        GROUP BY u.id
        ORDER BY u.username ASC
        LIMIT 20
    ');
    
    $search_pattern = '%' . $search_query . '%';
    $stmt->execute([
        ':user_id_join' => $user_id,
        ':user_id_filter' => $user_id,
        ':search1' => $search_pattern,
        ':search2' => $search_pattern,
        ':search3' => $search_pattern
    ]);
    
    $profiles = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'profiles' => $profiles
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>  