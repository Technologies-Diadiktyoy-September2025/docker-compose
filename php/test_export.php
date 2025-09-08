<?php
// Test script to verify export functionality
session_start();
require_once __DIR__ . '/db.php';

// Set a test user session (you can modify this for testing)
if (!isset($_SESSION['user_id'])) {
    // For testing purposes, set a test user
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'test_user';
    $_SESSION['first_name'] = 'Test';
    $_SESSION['last_name'] = 'User';
    $_SESSION['email'] = 'test@example.com';
}

echo "<h1>Export Test</h1>";
echo "<p>Testing YAML export functionality...</p>";

try {
    // Test the database query
    $query = "
        SELECT 
            ul.id as list_id,
            ul.list_name,
            ul.description,
            ul.created_at as list_created_at,
            ul.is_public,
            u.username,
            u.first_name,
            u.last_name,
            u.email,
            u.created_at as user_created_at,
            sc.id as content_id,
            sc.title as video_title,
            sc.description as video_description,
            sc.youtube_video_id,
            sc.channel_name,
            sc.thumbnail_url,
            sc.published_at,
            li.position,
            li.added_at
        FROM user_lists ul
        JOIN users u ON ul.user_id = u.id
        LEFT JOIN list_items li ON ul.id = li.list_id
        LEFT JOIN streaming_content sc ON li.content_id = sc.id
        ORDER BY ul.id, li.position
        LIMIT 5
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Database Query Test</h2>";
    echo "<p>Found " . count($results) . " records</p>";
    
    if (count($results) > 0) {
        echo "<h3>Sample Data:</h3>";
        echo "<pre>";
        print_r($results[0]);
        echo "</pre>";
        
        // Test user hashing
        $row = $results[0];
        $user_key = $row['username'] . $row['email'] . $row['user_created_at'];
        $user_hash = 'user_' . substr(hash('sha256', $user_key), 0, 12);
        
        echo "<h3>Privacy Protection Test:</h3>";
        echo "<p><strong>Original username:</strong> " . htmlspecialchars($row['username']) . "</p>";
        echo "<p><strong>Original email:</strong> " . htmlspecialchars($row['email']) . "</p>";
        echo "<p><strong>Generated identifier:</strong> " . htmlspecialchars($user_hash) . "</p>";
        
        echo "<h3>Export Links:</h3>";
        echo "<p><a href='export_yaml.php' target='_blank'>Test YAML Export</a></p>";
        echo "<p><a href='export_data.php'>Go to Export Interface</a></p>";
        
    } else {
        echo "<p>No data found in database. Make sure you have some lists and content.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Database Tables Check:</h2>";
try {
    $tables = ['users', 'user_lists', 'list_items', 'streaming_content'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "<p><strong>$table:</strong> $count records</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking tables: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
