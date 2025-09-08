<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Complete SPA Database Debug</h1>";

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
    
    // Test all tables
    $tables = ['users', 'user_lists', 'streaming_content', 'list_items', 'user_follows', 'youtube_credentials'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
    
    // Test the exact queries that the SPA uses
    echo "<h2>Testing SPA API Queries</h2>";
    
    // Test lists query (from api/lists.php)
    try {
        $stmt = $pdo->prepare("
            SELECT ul.*, 
                   COUNT(li.id) as item_count,
                   u.username as creator_username
            FROM user_lists ul
            LEFT JOIN list_items li ON ul.id = li.list_id
            LEFT JOIN users u ON ul.user_id = u.id
            WHERE ul.user_id = ? OR ul.is_public = 1
            GROUP BY ul.id
            ORDER BY ul.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $lists = $stmt->fetchAll();
        echo "<p style='color: green;'>✅ Lists query successful - found " . count($lists) . " lists</p>";
        
        if (count($lists) > 0) {
            echo "<h3>Your Lists:</h3>";
            foreach ($lists as $list) {
                echo "<p>- " . htmlspecialchars($list['list_name']) . " (ID: " . $list['id'] . ")</p>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Lists query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Test create list functionality
    echo "<h2>Testing List Creation</h2>";
    try {
        // Simulate creating a test list
        $stmt = $pdo->prepare("
            INSERT INTO user_lists (user_id, list_name, description, is_public) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], 'Test List', 'Test Description', 0]);
        $list_id = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Test list created successfully (ID: $list_id)</p>";
        
        // Clean up - delete the test list
        $stmt = $pdo->prepare("DELETE FROM user_lists WHERE id = ?");
        $stmt->execute([$list_id]);
        echo "<p style='color: green;'>✅ Test list cleaned up</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ List creation test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Test API endpoint directly
    echo "<h2>Testing API Endpoint</h2>";
    echo "<p><a href='api/lists.php' target='_blank'>Test API Endpoint</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Error details:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Next Steps</h2>";
echo "<p><a href='spa.php'>Try SPA</a></p>";
echo "<p><a href='debug_authenticated.php'>Run Authenticated Debug</a></p>";
echo "<p><a href='logout.php'>Logout</a></p>";
?>
