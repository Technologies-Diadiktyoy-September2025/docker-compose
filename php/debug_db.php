<?php
echo "<h1>Database Connection Debug</h1>";

// Check if PDO MySQL extension is available
if (!extension_loaded('pdo_mysql')) {
    echo "<p style='color: red;'>❌ PDO MySQL extension is not loaded!</p>";
    echo "<p>You need to enable the pdo_mysql extension in your PHP configuration.</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ PDO MySQL extension is loaded</p>";
}

// Test connection parameters
$DB_HOST = 'localhost';
$DB_NAME = 'my_database';
$DB_USER = 'root';
$DB_PASS = '';

echo "<h3>Connection Parameters:</h3>";
echo "<ul>";
echo "<li>Host: $DB_HOST</li>";
echo "<li>Database: $DB_NAME</li>";
echo "<li>User: $DB_USER</li>";
echo "<li>Password: " . (empty($DB_PASS) ? '(empty)' : '(set)') . "</li>";
echo "</ul>";

// Test connection without database first
try {
    $dsn = "mysql:host={$DB_HOST};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    echo "<p style='color: green;'>✅ MySQL server connection successful!</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$DB_NAME'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Database '$DB_NAME' exists!</p>";
        
        // Try to connect to the specific database
        try {
            $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
            $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
            echo "<p style='color: green;'>✅ Database connection successful!</p>";
            
            // Check if users table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✅ Users table exists!</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Users table does not exist. You need to create it.</p>";
            }
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Database '$DB_NAME' does not exist!</p>";
        echo "<p>You need to create the database first.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL server connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Possible Solutions:</h3>";
    echo "<ul>";
    echo "<li>Make sure MySQL is running (check XAMPP Control Panel)</li>";
    echo "<li>Check if MySQL is running on port 3306</li>";
    echo "<li>Verify the username and password</li>";
    echo "<li>Make sure MySQL service is started</li>";
    echo "</ul>";
}

// Show PHP info
echo "<h3>PHP Configuration:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "</p>";
?>
