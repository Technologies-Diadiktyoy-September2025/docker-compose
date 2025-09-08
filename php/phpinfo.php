<?php
echo "<h1>PHP Information</h1>";
echo "<p>This will help us see what PHP configuration the web server is using.</p>";
echo "<p><strong>PDO MySQL Available:</strong> " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Available PDO Drivers:</strong> " . implode(', ', PDO::getAvailableDrivers()) . "</p>";

// Test database connection
try {
    require_once __DIR__ . '/db.php';
    echo "<p style='color: green;'><strong>Database Connection:</strong> SUCCESS!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Database Connection:</strong> FAILED - " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Show full PHP info (remove this line if you don't want to see all PHP settings)
phpinfo();
?>
