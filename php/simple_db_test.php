<?php
// Simple database test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Database Test</h1>";

// Test database connection step by step
$DB_HOST = 'localhost';
$DB_NAME = 'my_database';
$DB_USER = 'root';
$DB_PASS = '';

echo "<h2>Testing Connection Parameters:</h2>";
echo "<p>Host: $DB_HOST</p>";
echo "<p>Database: $DB_NAME</p>";
echo "<p>User: $DB_USER</p>";
echo "<p>Password: " . (empty($DB_PASS) ? '(empty)' : '(set)') . "</p>";

try {
    // Test connection without database first
    echo "<h2>Step 1: Testing MySQL Connection</h2>";
    $dsn = "mysql:host={$DB_HOST};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<p style='color: green;'>✅ MySQL connection successful!</p>";
    
    // Test if database exists
    echo "<h2>Step 2: Checking if database exists</h2>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$DB_NAME'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Database '$DB_NAME' exists!</p>";
        
        // Test connection to specific database
        echo "<h2>Step 3: Testing database connection</h2>";
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Check tables
        echo "<h2>Step 4: Checking tables</h2>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Found " . count($tables) . " tables:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>❌ Database '$DB_NAME' does not exist!</p>";
        echo "<p>You need to create the database first.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Common solutions:</h3>";
    echo "<ul>";
    echo "<li>Make sure MySQL is running in XAMPP</li>";
    echo "<li>Check if the database 'my_database' exists</li>";
    echo "<li>Verify MySQL credentials (usually root with no password for XAMPP)</li>";
    echo "</ul>";
}
?>
