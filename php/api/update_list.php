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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Handle JSON data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$list_name = trim($input['list_name'] ?? '');
$description = trim($input['description'] ?? '');
$is_public = isset($input['is_public']) ? 1 : 0;

if ($list_name === '') {
    echo json_encode(['success' => false, 'message' => 'List name is required']);
    exit;
}

try {
    // First, check if the list exists and belongs to the user
    $stmt = $pdo->prepare('SELECT id FROM user_lists WHERE id = ? AND user_id = ?');
    $stmt->execute([$list_id, $user_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'List not found or access denied']);
        exit;
    }
    
    // Update the list
    $stmt = $pdo->prepare('UPDATE user_lists SET list_name = ?, description = ?, is_public = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->execute([$list_name, $description, $is_public, $list_id, $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'List updated successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    error_log('Update list error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update list: ' . $e->getMessage()
    ]);
}
?>
