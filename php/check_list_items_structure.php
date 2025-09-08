<?php
require_once __DIR__ . '/db.php';

echo "<h1>List Items Table Structure</h1>";

try {
    // Check the structure of list_items table
    $stmt = $pdo->query("DESCRIBE list_items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Columns in list_items table:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Also check if there are any records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM list_items");
    $count = $stmt->fetch()['count'];
    echo "<p><strong>Total records in list_items:</strong> $count</p>";
    
    if ($count > 0) {
        echo "<h2>Sample record from list_items:</h2>";
        $stmt = $pdo->query("SELECT * FROM list_items LIMIT 1");
        $sample = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($sample);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>