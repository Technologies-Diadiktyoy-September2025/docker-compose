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

// Handle both JSON and form data
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    // JSON data from SPA
    $list_name = trim($input['list_name'] ?? '');
    $description = trim($input['description'] ?? '');
    $is_public = isset($input['is_public']) ? 1 : 0;
} else {
    // Form data from regular PHP pages
    $list_name = trim($_POST['list_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
}

if ($list_name === '') {
    echo json_encode(['success' => false, 'message' => 'List name is required']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO user_lists (user_id, list_name, description, is_public) VALUES (:user_id, :list_name, :description, :is_public)');
    $stmt->execute([
        ':user_id' => $user_id,
        ':list_name' => $list_name,
        ':description' => $description,
        ':is_public' => $is_public
    ]);
    
    echo json_encode(['success' => true, 'message' => 'List created successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    // Log the actual error for debugging (remove in production)
    error_log('Create list error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to create list: ' . $e->getMessage()
    ]);
}
?>
