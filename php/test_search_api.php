<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/youtube_config.php';

echo "<h1>YouTube Search API Test</h1>";

if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ User not logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p>✅ User ID: " . $user_id . "</p>";

// Test the search API endpoint
echo "<h2>Testing Search API Endpoint</h2>";

if (isset($_POST['test_query'])) {
    $query = $_POST['test_query'];
    echo "<p>Testing search for: " . htmlspecialchars($query) . "</p>";
    
    try {
        // Simulate the API call
        $stmt = $pdo->prepare('SELECT access_token FROM youtube_credentials WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $user_id]);
        $credentials = $stmt->fetch();
        
        if (!$credentials) {
            echo "<p>❌ YouTube credentials not found</p>";
        } else {
            echo "<p>✅ YouTube credentials found</p>";
            
            // Test the searchYouTubeVideos function
            echo "<p>Calling searchYouTubeVideos function...</p>";
            $search_results = searchYouTubeVideos($query, $credentials['access_token'], 5);
            
            if ($search_results && isset($search_results['items'])) {
                echo "<p>✅ Search successful! Found " . count($search_results['items']) . " results</p>";
                
                echo "<h3>Search Results:</h3>";
                foreach ($search_results['items'] as $index => $item) {
                    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
                    echo "<h4>" . ($index + 1) . ". " . htmlspecialchars($item['snippet']['title']) . "</h4>";
                    echo "<p><strong>Channel:</strong> " . htmlspecialchars($item['snippet']['channelTitle']) . "</p>";
                    echo "<p><strong>Description:</strong> " . htmlspecialchars(substr($item['snippet']['description'], 0, 100)) . "...</p>";
                    echo "<p><strong>Video ID:</strong> " . $item['id']['videoId'] . "</p>";
                    echo "<p><strong>Published:</strong> " . $item['snippet']['publishedAt'] . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>❌ Search failed or no results</p>";
                if ($search_results) {
                    echo "<p>Response: " . print_r($search_results, true) . "</p>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
        echo "<p>Stack trace:</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

echo "<h2>Test Search</h2>";
echo "<form method='post'>";
echo "<label for='test_query'>Search Query:</label><br>";
echo "<input type='text' id='test_query' name='test_query' value='music' style='width: 300px; padding: 5px;'><br><br>";
echo "<button type='submit' style='padding: 10px 20px;'>Test Search</button>";
echo "</form>";

echo "<h2>API Endpoint Test</h2>";
echo "<p>You can also test the API endpoint directly:</p>";
echo "<p><a href='api/search_videos.php' target='_blank'>api/search_videos.php</a></p>";
?>
