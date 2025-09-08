<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Authenticated Database Debug</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Not logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit;
}

echo "<p style='color: green;'>✅ User is logged in</p>";
echo "<p>User ID: " . htmlspecialchars($_SESSION['user_id']) . "</p>";
echo "<p>Username: " . htmlspecialchars($_SESSION['username']) . "</p>";

// Test database connection
try {
    require_once __DIR__ . '/db.php';
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if user exists in database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p style='color: green;'>✅ User found in database</p>";
    } else {
        echo "<p style='color: red;'>❌ User not found in database</p>";
    }
    
    // Test streaming tables
    $tables = ['user_lists', 'streaming_content', 'list_items'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
    
    // Test API endpoint simulation
    echo "<h2>Testing API Endpoint Simulation</h2>";
    
    // Simulate what the lists API does
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
        
        echo "<p style='color: green;'>✅ API query successful - found " . count($lists) . " lists</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ API query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Error details:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<p><a href='spa.php'>Try SPA again</a></p>";
echo "<p><a href='logout.php'>Logout</a></p>";
?>
