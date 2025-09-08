<?php
// Debug script to check database connection for SPA
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>SPA Database Debug</h1>";

try {
    require_once __DIR__ . '/db.php';
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Check if all required tables exist
    $tables = ['users', 'user_lists', 'streaming_content', 'list_items', 'user_follows', 'youtube_credentials'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
    
    // Test a simple query that SPA might use
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_lists");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Can query user_lists table: " . $result['count'] . " lists found</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Error details:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<p><a href='spa.php'>Try SPA again</a></p>";
?>
