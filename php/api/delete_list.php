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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$list_id = $input['list_id'] ?? '';

if (!$list_id || !is_numeric($list_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid list ID']);
    exit;
}

try {
    // Verify user owns the list
    $stmt = $pdo->prepare('SELECT id FROM user_lists WHERE id = :list_id AND user_id = :user_id');
    $stmt->execute([':list_id' => $list_id, ':user_id' => $user_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'List not found or access denied']);
        exit;
    }
    
    // Delete the list (cascade will handle related records)
    $stmt = $pdo->prepare('DELETE FROM user_lists WHERE id = :list_id AND user_id = :user_id');
    $stmt->execute([':list_id' => $list_id, ':user_id' => $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'List deleted successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete list']);
}
?>
