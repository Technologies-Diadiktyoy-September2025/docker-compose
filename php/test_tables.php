<?php
require_once __DIR__ . '/db.php';

echo "<h1>Table Test</h1>";

try {
    // Test if tables exist
    $tables = ['user_lists', 'streaming_content', 'list_items', 'user_follows', 'youtube_credentials'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
    
    // Test a simple insert to user_lists
    if (in_array('user_lists', $tables)) {
        echo "<h2>Testing user_lists table</h2>";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_lists");
        $result = $stmt->fetch();
        echo "<p>Current lists: " . $result['count'] . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
