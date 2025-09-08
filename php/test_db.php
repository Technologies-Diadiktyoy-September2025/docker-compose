<?php
require_once __DIR__ . '/db.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test basic connection
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Users table exists!</p>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Users table does not exist!</p>";
        echo "<p>Please run the SQL from users.sql to create the table.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Make sure MySQL is running in XAMPP</li>";
    echo "<li>Check that the database 'my_database' exists</li>";
    echo "<li>Verify the database credentials in db.php</li>";
    echo "</ul>";
}
?>
