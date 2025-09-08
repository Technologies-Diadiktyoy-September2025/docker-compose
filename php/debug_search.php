<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];

echo "<h1>Search Debug</h1>";

// Test 1: Check if we have any lists
echo "<h2>Test 1: Lists</h2>";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM user_lists");
$result = $stmt->fetch();
echo "Total lists: " . $result['count'] . "<br>";

// Test 2: Check if we have any streaming content
echo "<h2>Test 2: Streaming Content</h2>";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM streaming_content");
$result = $stmt->fetch();
echo "Total streaming content: " . $result['count'] . "<br>";

// Test 3: Check if we have any list items
echo "<h2>Test 3: List Items</h2>";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM list_items");
$result = $stmt->fetch();
echo "Total list items: " . $result['count'] . "<br>";

// Test 4: Show sample data
echo "<h2>Test 4: Sample Lists</h2>";
$stmt = $pdo->query("SELECT ul.id, ul.list_name, ul.description, u.username FROM user_lists ul JOIN users u ON ul.user_id = u.id LIMIT 5");
$results = $stmt->fetchAll();
foreach ($results as $row) {
    echo "List: " . htmlspecialchars($row['list_name']) . " by " . htmlspecialchars($row['username']) . "<br>";
}

echo "<h2>Test 5: Sample Streaming Content</h2>";
$stmt = $pdo->query("SELECT id, title, description FROM streaming_content LIMIT 5");
$results = $stmt->fetchAll();
foreach ($results as $row) {
    echo "Content: " . htmlspecialchars($row['title']) . "<br>";
}

echo "<h2>Test 6: Sample List Items</h2>";
$stmt = $pdo->query("SELECT li.id, ul.list_name, sc.title FROM list_items li JOIN user_lists ul ON li.list_id = ul.id JOIN streaming_content sc ON li.content_id = sc.id LIMIT 5");
$results = $stmt->fetchAll();
foreach ($results as $row) {
    echo "List '" . htmlspecialchars($row['list_name']) . "' contains: " . htmlspecialchars($row['title']) . "<br>";
}

echo "<h2>Test 7: Full JOIN Test</h2>";
$stmt = $pdo->query("
    SELECT DISTINCT
        ul.id,
        ul.list_name,
        ul.description,
        sc.title as video_title,
        sc.description as video_description
    FROM user_lists ul
    INNER JOIN users u ON ul.user_id = u.id
    LEFT JOIN list_items li ON ul.id = li.list_id
    LEFT JOIN streaming_content sc ON li.content_id = sc.id
    WHERE (ul.is_public = 1 OR ul.user_id = $current_user_id)
    LIMIT 10
");
$results = $stmt->fetchAll();
foreach ($results as $row) {
    echo "List: " . htmlspecialchars($row['list_name']) . "<br>";
    if ($row['video_title']) {
        echo "  - Video: " . htmlspecialchars($row['video_title']) . "<br>";
    }
    echo "<br>";
}
?>
