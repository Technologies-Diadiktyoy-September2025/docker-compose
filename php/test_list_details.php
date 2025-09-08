<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>List Details Test</h1>";

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
    
    // First, let's see what lists exist
    $stmt = $pdo->prepare("SELECT * FROM user_lists WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $lists = $stmt->fetchAll();
    
    echo "<h2>Your Lists:</h2>";
    if (count($lists) > 0) {
        foreach ($lists as $list) {
            echo "<p>- " . htmlspecialchars($list['list_name']) . " (ID: " . $list['id'] . ")</p>";
        }
        
        // Test getting details for the first list
        $first_list = $lists[0];
        echo "<h2>Testing List Details for: " . htmlspecialchars($first_list['list_name']) . "</h2>";
        
        // Test the exact query from get_list.php
        $stmt = $pdo->prepare('
            SELECT ul.*, u.username, u.first_name, u.last_name
            FROM user_lists ul
            LEFT JOIN users u ON ul.user_id = u.id
            WHERE ul.id = ?
        ');
        $stmt->execute([$first_list['id']]);
        $list_details = $stmt->fetch();
        
        if ($list_details) {
            echo "<p style='color: green;'>✅ List details query successful</p>";
            echo "<p>List Name: " . htmlspecialchars($list_details['list_name']) . "</p>";
            echo "<p>Owner: " . htmlspecialchars($list_details['username']) . "</p>";
            
            // Test the videos query
            $stmt = $pdo->prepare('
                SELECT sc.*, li.position_order, li.added_at as added_to_list_at
                FROM list_items li
                INNER JOIN streaming_content sc ON li.content_id = sc.id
                WHERE li.list_id = ?
                ORDER BY li.position_order ASC, li.added_at ASC
            ');
            $stmt->execute([$first_list['id']]);
            $videos = $stmt->fetchAll();
            
            echo "<p style='color: green;'>✅ Videos query successful - found " . count($videos) . " videos</p>";
            
        } else {
            echo "<p style='color: red;'>❌ List details query failed</p>";
        }
        
    } else {
        echo "<p>No lists found. Create a list first.</p>";
    }
    
    // Test the API endpoint directly
    echo "<h2>Test API Endpoint</h2>";
    if (count($lists) > 0) {
        $list_id = $lists[0]['id'];
        echo "<p><a href='api/get_list.php?id=$list_id' target='_blank'>Test API: api/get_list.php?id=$list_id</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Error details:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<p><a href='spa.php'>Go to SPA</a></p>";
?>
