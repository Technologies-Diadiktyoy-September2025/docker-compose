<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/youtube_config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

echo "<h1>Update Video Titles</h1>";
echo "<p>This script will fetch real YouTube video titles and update the database.</p>";

// Check if YouTube API is configured
if (empty(YOUTUBE_API_KEY)) {
    echo "<p style='color: red;'>YouTube API key not configured. Please set up YouTube API first.</p>";
    exit;
}

try {
    // Get all streaming content that needs title updates
    $stmt = $pdo->query("
        SELECT id, youtube_video_id, title 
        FROM streaming_content 
        WHERE title LIKE 'video%' OR title = '' OR title IS NULL
        ORDER BY id
    ");
    
    $videos = $stmt->fetchAll();
    
    echo "<p>Found " . count($videos) . " videos that need title updates.</p>";
    
    if (count($videos) == 0) {
        echo "<p style='color: green;'>All video titles are already up to date!</p>";
        exit;
    }
    
    $updated = 0;
    $errors = 0;
    
    foreach ($videos as $video) {
        echo "<p>Updating video ID: " . htmlspecialchars($video['youtube_video_id']) . " (Current title: " . htmlspecialchars($video['title']) . ")</p>";
        
        try {
            // Fetch video details from YouTube API
            $url = "https://www.googleapis.com/youtube/v3/videos?id=" . urlencode($video['youtube_video_id']) . "&key=" . YOUTUBE_API_KEY . "&part=snippet";
            
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            if (isset($data['items'][0]['snippet'])) {
                $snippet = $data['items'][0]['snippet'];
                $new_title = $snippet['title'];
                $new_description = $snippet['description'] ?? '';
                $new_channel_title = $snippet['channelTitle'] ?? '';
                $new_thumbnail_url = $snippet['thumbnails']['medium']['url'] ?? '';
                $new_published_at = $snippet['publishedAt'] ?? '';
                
                // Update the database
                $update_stmt = $pdo->prepare("
                    UPDATE streaming_content 
                    SET title = :title, 
                        description = :description, 
                        channel_title = :channel_title,
                        thumbnail_url = :thumbnail_url,
                        published_at = :published_at
                    WHERE id = :id
                ");
                
                $update_stmt->execute([
                    ':title' => $new_title,
                    ':description' => $new_description,
                    ':channel_title' => $new_channel_title,
                    ':thumbnail_url' => $new_thumbnail_url,
                    ':published_at' => $new_published_at,
                    ':id' => $video['id']
                ]);
                
                echo "<p style='color: green;'>✓ Updated: " . htmlspecialchars($new_title) . "</p>";
                $updated++;
                
            } else {
                echo "<p style='color: orange;'>⚠ No data found for video ID: " . htmlspecialchars($video['youtube_video_id']) . "</p>";
                $errors++;
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error updating video " . htmlspecialchars($video['youtube_video_id']) . ": " . htmlspecialchars($e->getMessage()) . "</p>";
            $errors++;
        }
        
        // Add a small delay to avoid hitting API rate limits
        sleep(1);
    }
    
    echo "<hr>";
    echo "<h2>Summary</h2>";
    echo "<p><strong>Updated:</strong> $updated videos</p>";
    echo "<p><strong>Errors:</strong> $errors videos</p>";
    
    if ($updated > 0) {
        echo "<p style='color: green;'><strong>Video titles have been updated! The search should now work properly.</strong></p>";
        echo "<p><a href='advanced_search.php'>Try the advanced search now</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
