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

$current_user_id = $_SESSION['user_id'];
$user_id = $_GET['user_id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    // Get user information
    $stmt = $pdo->prepare('
        SELECT id, username, first_name, last_name, created_at
        FROM users 
        WHERE id = :user_id
    ');
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Get user's public lists
    $stmt = $pdo->prepare('
        SELECT ul.*, 
               COUNT(li.id) as item_count
        FROM user_lists ul
        LEFT JOIN list_items li ON ul.id = li.list_id
        WHERE ul.user_id = :user_id AND ul.is_public = 1
        GROUP BY ul.id
        ORDER BY ul.updated_at DESC
    ');
    $stmt->execute([':user_id' => $user_id]);
    $lists = $stmt->fetchAll();
    
    // Check if current user is following this user
    $stmt = $pdo->prepare('
        SELECT id FROM user_follows 
        WHERE follower_id = :current_user_id AND following_id = :user_id
    ');
    $stmt->execute([
        ':current_user_id' => $current_user_id,
        ':user_id' => $user_id
    ]);
    $is_following = $stmt->fetch() ? true : false;
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'lists' => $lists,
        'is_following' => $is_following
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
