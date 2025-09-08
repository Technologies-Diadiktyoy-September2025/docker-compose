<?php
session_start();
require_once __DIR__ . '/db.php';

// Test script to debug create list functionality
echo "<h1>Create List Debug Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ User not logged in</p>";
    echo "<p><a href='login.php'>Login first</a></p>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p style='color: green;'>✅ User logged in: " . htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') . "</p>";

// Check if user_lists table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_lists'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ user_lists table exists</p>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE user_lists");
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
        echo "<p style='color: red;'>❌ user_lists table does not exist!</p>";
        echo "<p>Please run <a href='setup_streaming.php'>setup_streaming.php</a> to create the tables.</p>";
        exit;
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    exit;
}

// Test creating a list
echo "<h3>Test Create List:</h3>";
try {
    $test_name = 'Test List ' . date('Y-m-d H:i:s');
    $test_description = 'This is a test list created by the debug script';
    
    $stmt = $pdo->prepare('INSERT INTO user_lists (user_id, list_name, description, is_public) VALUES (:user_id, :list_name, :description, :is_public)');
    $stmt->execute([
        ':user_id' => $user_id,
        ':list_name' => $test_name,
        ':description' => $test_description,
        ':is_public' => 0
    ]);
    
    $list_id = $pdo->lastInsertId();
    echo "<p style='color: green;'>✅ Test list created successfully with ID: $list_id</p>";
    
    // Clean up - delete the test list
    $stmt = $pdo->prepare('DELETE FROM user_lists WHERE id = :id');
    $stmt->execute([':id' => $list_id]);
    echo "<p style='color: blue;'>ℹ️ Test list deleted (cleanup)</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Failed to create test list: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
}

echo "<h3>API Test:</h3>";
echo "<p>Test the API endpoint directly:</p>";
echo "<form method='post' action='api/create_list.php' style='border: 1px solid #ccc; padding: 20px; margin: 20px 0;'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>List Name: <input type='text' name='list_name' value='API Test List' required></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Description: <textarea name='description'>API test description</textarea></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label><input type='checkbox' name='is_public' value='1'> Make public</label>";
echo "</div>";
echo "<button type='submit'>Test Create List API</button>";
echo "</form>";

echo "<p><a href='spa.php'>← Back to SPA</a></p>";
?>
