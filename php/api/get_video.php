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
$content_id = $_GET['id'] ?? null;
$list_id = $_GET['list_id'] ?? null;

if (!$content_id || !is_numeric($content_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid video ID']);
    exit;
}

try {
    // Get video information
    $stmt = $pdo->prepare('SELECT * FROM streaming_content WHERE id = :content_id');
    $stmt->execute([':content_id' => $content_id]);
    $video = $stmt->fetch();
    
    if (!$video) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Video not found']);
        exit;
    }
    
    $playlist = [];
    
    // If list_id is provided, get playlist information
    if ($list_id && is_numeric($list_id)) {
        // Get list information
        $stmt = $pdo->prepare('
            SELECT ul.*, u.username
            FROM user_lists ul
            LEFT JOIN users u ON ul.user_id = u.id
            WHERE ul.id = :list_id
        ');
        $stmt->execute([':list_id' => $list_id]);
        $list = $stmt->fetch();
        
        if ($list) {
            // Check if user can view this list
            if ($list['user_id'] == $user_id || $list['is_public']) {
                // Get all videos in the list for playlist
                $stmt = $pdo->prepare('
                    SELECT sc.*, li.added_at
                    FROM list_items li
                    INNER JOIN streaming_content sc ON li.content_id = sc.id
                    WHERE li.list_id = :list_id
                    ORDER BY li.added_at ASC
                ');
                $stmt->execute([':list_id' => $list_id]);
                $playlist = $stmt->fetchAll();
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'video' => $video,
        'playlist' => $playlist
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
