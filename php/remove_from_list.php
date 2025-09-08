<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = $_GET['item_id'] ?? null;
$list_id = $_GET['list_id'] ?? null;

if (!$item_id || !$list_id || !is_numeric($item_id) || !is_numeric($list_id)) {
    header('Location: my_lists.php');
    exit;
}

try {
    // Verify user owns the list
    $stmt = $pdo->prepare('SELECT id FROM user_lists WHERE id = :list_id AND user_id = :user_id');
    $stmt->execute([':list_id' => $list_id, ':user_id' => $user_id]);
    
    if (!$stmt->fetch()) {
        header('Location: my_lists.php');
        exit;
    }
    
    // Remove item from list
    $stmt = $pdo->prepare('DELETE FROM list_items WHERE list_id = :list_id AND content_id = :item_id');
    $stmt->execute([':list_id' => $list_id, ':item_id' => $item_id]);
    
    // Redirect back to list
    header('Location: view_list.php?id=' . $list_id);
    exit;
    
} catch (PDOException $e) {
    header('Location: my_lists.php');
    exit;
}
?>
