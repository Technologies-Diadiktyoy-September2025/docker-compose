<?php
require_once __DIR__ . '/db.php';

echo "<h1>Manual Table Creation</h1>";

try {
    // Create user_lists table
    $sql1 = "CREATE TABLE IF NOT EXISTS user_lists (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        list_name VARCHAR(255) NOT NULL,
        description TEXT,
        is_public BOOLEAN DEFAULT FALSE,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql1);
    echo "<p style='color: green;'>✅ Created user_lists table</p>";
    
    // Create streaming_content table
    $sql2 = "CREATE TABLE IF NOT EXISTS streaming_content (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        youtube_video_id VARCHAR(50) NOT NULL,
        title VARCHAR(500) NOT NULL,
        description TEXT,
        thumbnail_url VARCHAR(500),
        duration VARCHAR(20),
        channel_title VARCHAR(255),
        published_at DATETIME,
        added_by_user_id INT UNSIGNED NOT NULL,
        added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_video (youtube_video_id),
        FOREIGN KEY (added_by_user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql2);
    echo "<p style='color: green;'>✅ Created streaming_content table</p>";
    
    // Create list_items table
    $sql3 = "CREATE TABLE IF NOT EXISTS list_items (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        list_id INT UNSIGNED NOT NULL,
        content_id INT UNSIGNED NOT NULL,
        added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_list_content (list_id, content_id),
        FOREIGN KEY (list_id) REFERENCES user_lists(id) ON DELETE CASCADE,
        FOREIGN KEY (content_id) REFERENCES streaming_content(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql3);
    echo "<p style='color: green;'>✅ Created list_items table</p>";
    
    // Create user_follows table
    $sql4 = "CREATE TABLE IF NOT EXISTS user_follows (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        follower_id INT UNSIGNED NOT NULL,
        following_id INT UNSIGNED NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_follow (follower_id, following_id),
        FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql4);
    echo "<p style='color: green;'>✅ Created user_follows table</p>";
    
    // Create youtube_credentials table
    $sql5 = "CREATE TABLE IF NOT EXISTS youtube_credentials (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        access_token TEXT,
        refresh_token TEXT,
        expires_at DATETIME,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_user (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql5);
    echo "<p style='color: green;'>✅ Created youtube_credentials table</p>";
    
    echo "<h2>All tables created successfully!</h2>";
    echo "<p><a href='test_tables.php'>Test tables</a></p>";
    echo "<p><a href='spa.php'>Go to SPA</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
