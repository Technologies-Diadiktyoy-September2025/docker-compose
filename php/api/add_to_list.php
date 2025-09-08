<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../youtube_config.php';

header('Content-Type: application/json');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$video_id = $input['video_id'] ?? '';
$list_id = $input['list_id'] ?? '';

if (!$video_id || !$list_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Verify user owns the list
    $stmt = $pdo->prepare('SELECT id FROM user_lists WHERE id = :list_id AND user_id = :user_id');
    $stmt->execute([':list_id' => $list_id, ':user_id' => $user_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'List not found or access denied']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    // Check if content already exists
    $stmt = $pdo->prepare('SELECT id FROM streaming_content WHERE youtube_video_id = :video_id');
    $stmt->execute([':video_id' => $video_id]);
    $existing_content = $stmt->fetch();
    
    if ($existing_content) {
        $content_id = $existing_content['id'];
    } else {
        // Fetch video details from YouTube API
        $video_details = null;
        
        if (!empty(YOUTUBE_API_KEY)) {
            try {
                $url = "https://www.googleapis.com/youtube/v3/videos?id=" . urlencode($video_id) . "&key=" . YOUTUBE_API_KEY . "&part=snippet";
                $response = file_get_contents($url);
                $data = json_decode($response, true);
                
                if (isset($data['items'][0]['snippet'])) {
                    $video_details = $data['items'][0]['snippet'];
                }
            } catch (Exception $e) {
                // If API call fails, we'll use placeholder data
                error_log("Failed to fetch video details for $video_id: " . $e->getMessage());
            }
        }
        
        // Insert new content with real or placeholder data
        $title = $video_details['title'] ?? 'Video ' . $video_id;
        $description = $video_details['description'] ?? '';
        $channel_title = $video_details['channelTitle'] ?? '';
        $thumbnail_url = $video_details['thumbnails']['medium']['url'] ?? '';
        $published_at = $video_details['publishedAt'] ?? null;
        
        $stmt = $pdo->prepare('
            INSERT INTO streaming_content (youtube_video_id, title, description, channel_title, thumbnail_url, published_at, added_by_user_id) 
            VALUES (:video_id, :title, :description, :channel_title, :thumbnail_url, :published_at, :user_id)
        ');
        $stmt->execute([
            ':video_id' => $video_id,
            ':title' => $title,
            ':description' => $description,
            ':channel_title' => $channel_title,
            ':thumbnail_url' => $thumbnail_url,
            ':published_at' => $published_at ? date('Y-m-d H:i:s', strtotime($published_at)) : null,
            ':user_id' => $user_id
        ]);
        $content_id = $pdo->lastInsertId();
    }
    
    // Check if already in list
    $stmt = $pdo->prepare('SELECT id FROM list_items WHERE list_id = :list_id AND content_id = :content_id');
    $stmt->execute([':list_id' => $list_id, ':content_id' => $content_id]);
    
    if (!$stmt->fetch()) {
        // Add to list
        $stmt = $pdo->prepare('
            INSERT INTO list_items (list_id, content_id) 
            VALUES (:list_id, :content_id)
        ');
        $stmt->execute([':list_id' => $list_id, ':content_id' => $content_id]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Video added to list successfully']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add video to list']);
}
?>
