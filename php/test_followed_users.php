<?php
session_start();
require_once __DIR__ . '/db.php';

// Test the followed users API
$user_id = $_SESSION['user_id'] ?? 1; // Use session user or default to 1 for testing

echo "<h2>Testing Followed Users API</h2>";
echo "<p>User ID: " . $user_id . "</p>";

try {
    // Test the query from get_followed_users.php
    $stmt = $pdo->prepare('
        SELECT u.id, u.username, u.first_name, u.last_name, u.created_at,
               COUNT(ul.id) as public_lists_count,
               uf.created_at as followed_at
        FROM user_follows uf
        INNER JOIN users u ON uf.following_id = u.id
        LEFT JOIN user_lists ul ON u.id = ul.user_id AND ul.is_public = 1
        WHERE uf.follower_id = :user_id
        GROUP BY u.id
        ORDER BY uf.created_at DESC
    ');
    
    $stmt->execute([':user_id' => $user_id]);
    $followed_users = $stmt->fetchAll();
    
    echo "<h3>Followed Users:</h3>";
    if (empty($followed_users)) {
        echo "<p>No followed users found.</p>";
    } else {
        foreach ($followed_users as $user) {
            echo "<p>User: " . $user['first_name'] . " " . $user['last_name'] . " (@" . $user['username'] . ") - " . $user['public_lists_count'] . " public lists</p>";
        }
    }
    
    // Test the query from lists.php for followed lists
    $stmt = $pdo->prepare('
        SELECT ul.*, 
               COUNT(li.id) as item_count,
               u.username
        FROM user_lists ul
        LEFT JOIN list_items li ON ul.id = li.list_id
        LEFT JOIN users u ON ul.user_id = u.id
        INNER JOIN user_follows uf ON ul.user_id = uf.following_id
        WHERE uf.follower_id = :user_id AND ul.is_public = 1
        GROUP BY ul.id
        ORDER BY ul.updated_at DESC
    ');
    $stmt->execute([':user_id' => $user_id]);
    $followed_lists = $stmt->fetchAll();
    
    echo "<h3>Followed Lists:</h3>";
    if (empty($followed_lists)) {
        echo "<p>No followed lists found.</p>";
    } else {
        foreach ($followed_lists as $list) {
            echo "<p>List: " . $list['list_name'] . " by @" . $list['username'] . " - " . $list['item_count'] . " items</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}
?>
