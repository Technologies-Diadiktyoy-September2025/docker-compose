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
$list_id = $_GET['id'] ?? null;

if (!$list_id || !is_numeric($list_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid list ID']);
    exit;
}

try {
    // Get list information
    $stmt = $pdo->prepare('
        SELECT ul.*, u.username, u.first_name, u.last_name
        FROM user_lists ul
        LEFT JOIN users u ON ul.user_id = u.id
        WHERE ul.id = :list_id
    ');
    $stmt->execute([':list_id' => $list_id]);
    $list = $stmt->fetch();
    
    if (!$list) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'List not found']);
        exit;
    }
    
    // Check if user can view this list (owner, public, or following the owner)
    $can_view = false;
    if ($list['user_id'] == $user_id) {
        $can_view = true; // Owner can always view
    } elseif ($list['is_public']) {
        $can_view = true; // Public lists can be viewed by anyone
    } else {
        // Check if user is following the list owner
        $stmt = $pdo->prepare('SELECT id FROM user_follows WHERE follower_id = :follower_id AND following_id = :following_id');
        $stmt->execute([
            ':follower_id' => $user_id,
            ':following_id' => $list['user_id']
        ]);
        $can_view = $stmt->fetch() !== false;
    }
    
    if (!$can_view) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. You must follow this user to view their private lists.']);
        exit;
    }
    
    // Get list items
    $stmt = $pdo->prepare('
        SELECT sc.*, li.added_at as added_to_list_at
        FROM list_items li
        INNER JOIN streaming_content sc ON li.content_id = sc.id
        WHERE li.list_id = :list_id
        ORDER BY li.added_at ASC
    ');
    $stmt->execute([':list_id' => $list_id]);
    $videos = $stmt->fetchAll();
    
    // Add ownership flag
    $list['is_owner'] = ($list['user_id'] == $user_id);
    
    echo json_encode([
        'success' => true,
        'list' => $list,
        'videos' => $videos
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
