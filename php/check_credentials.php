<?php
session_start();
require_once __DIR__ . '/db.php';

echo "<h1>YouTube Credentials Check</h1>";

if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ User not logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p>✅ User ID: " . $user_id . "</p>";

try {
    // Check if credentials exist
    $stmt = $pdo->prepare('SELECT * FROM youtube_credentials WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $user_id]);
    $credentials = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($credentials) {
        echo "<p>✅ YouTube credentials found!</p>";
        echo "<h2>Credentials Details:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>" . $credentials['id'] . "</td></tr>";
        echo "<tr><td>User ID</td><td>" . $credentials['user_id'] . "</td></tr>";
        echo "<tr><td>Access Token</td><td>" . substr($credentials['access_token'], 0, 20) . "...</td></tr>";
        echo "<tr><td>Refresh Token</td><td>" . ($credentials['refresh_token'] ? substr($credentials['refresh_token'], 0, 20) . "..." : 'None') . "</td></tr>";
        echo "<tr><td>Expires At</td><td>" . $credentials['expires_at'] . "</td></tr>";
        echo "<tr><td>Created At</td><td>" . $credentials['created_at'] . "</td></tr>";
        echo "<tr><td>Updated At</td><td>" . $credentials['updated_at'] . "</td></tr>";
        echo "</table>";
        
        // Check if token is expired
        $expiresAt = strtotime($credentials['expires_at']);
        $now = time();
        
        if ($expiresAt > $now) {
            echo "<p>✅ Token is valid (expires in " . ($expiresAt - $now) . " seconds)</p>";
        } else {
            echo "<p>❌ Token is expired (" . ($now - $expiresAt) . " seconds ago)</p>";
        }
        
    } else {
        echo "<p>❌ No YouTube credentials found for this user</p>";
        echo "<p><a href='youtube_connect.php'>Connect YouTube Account</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}
?>
