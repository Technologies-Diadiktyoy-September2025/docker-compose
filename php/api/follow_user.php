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
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

if ($user_id == $current_user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot follow yourself']);
    exit;
}

try {
    // Check if already following
    $stmt = $pdo->prepare('
        SELECT id FROM user_follows 
        WHERE follower_id = :current_user_id AND following_id = :user_id
    ');
    $stmt->execute([
        ':current_user_id' => $current_user_id,
        ':user_id' => $user_id
    ]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Already following this user']);
        exit;
    }
    
    // Add follow relationship
    $stmt = $pdo->prepare('
        INSERT INTO user_follows (follower_id, following_id, created_at) 
        VALUES (:current_user_id, :user_id, NOW())
    ');
    $stmt->execute([
        ':current_user_id' => $current_user_id,
        ':user_id' => $user_id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'User followed successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
