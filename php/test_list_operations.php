<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>List Operations Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Not logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit;
}

echo "<p style='color: green;'>✅ User is logged in (ID: " . $_SESSION['user_id'] . ")</p>";

try {
    require_once __DIR__ . '/db.php';
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test list creation
    echo "<h2>Testing List Creation</h2>";
    try {
        $stmt = $pdo->prepare('INSERT INTO user_lists (user_id, list_name, description, is_public) VALUES (?, ?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], 'Test List ' . date('H:i:s'), 'Test Description', 0]);
        $list_id = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Test list created successfully (ID: $list_id)</p>";
        
        // Test list editing
        echo "<h2>Testing List Editing</h2>";
        $stmt = $pdo->prepare('UPDATE user_lists SET list_name = ?, description = ? WHERE id = ? AND user_id = ?');
        $stmt->execute(['Updated Test List', 'Updated Description', $list_id, $_SESSION['user_id']]);
        echo "<p style='color: green;'>✅ Test list updated successfully</p>";
        
        // Clean up - delete the test list
        $stmt = $pdo->prepare('DELETE FROM user_lists WHERE id = ? AND user_id = ?');
        $stmt->execute([$list_id, $_SESSION['user_id']]);
        echo "<p style='color: green;'>✅ Test list cleaned up</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ List operation failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Test API endpoints
    echo "<h2>Testing API Endpoints</h2>";
    echo "<p><a href='api/create_list.php' target='_blank'>Test Create List API</a> (should show Method not allowed)</p>";
    echo "<p><a href='api/lists.php' target='_blank'>Test Lists API</a></p>";
    
    // Show current lists
    $stmt = $pdo->prepare("SELECT * FROM user_lists WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $lists = $stmt->fetchAll();
    
    echo "<h2>Your Current Lists:</h2>";
    if (count($lists) > 0) {
        foreach ($lists as $list) {
            echo "<p>- " . htmlspecialchars($list['list_name']) . " (ID: " . $list['id'] . ") - " . ($list['is_public'] ? 'Public' : 'Private') . "</p>";
        }
    } else {
        echo "<p>No lists found.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Error details:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>JavaScript Console Test</h2>";
echo "<p>Open your browser's Developer Tools (F12) and check the Console tab for any JavaScript errors when trying to create or edit lists.</p>";

echo "<p><a href='spa.php'>Go to SPA</a></p>";
?>
