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

// Check YouTube credentials
if (!hasValidYouTubeCredentials($user_id, $pdo)) {
    echo json_encode(['success' => false, 'message' => 'YouTube account not connected']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$query = trim($input['query'] ?? '');

if ($query === '') {
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit;
}

try {
    // Get user's access token
    $stmt = $pdo->prepare('SELECT access_token FROM youtube_credentials WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $user_id]);
    $credentials = $stmt->fetch();
    
    if (!$credentials) {
        echo json_encode(['success' => false, 'message' => 'YouTube credentials not found']);
        exit;
    }
    
    // Search YouTube
    $search_results = searchYouTubeVideos($query, $credentials['access_token'], 20);
    
    if (!$search_results || !isset($search_results['items'])) {
        echo json_encode(['success' => false, 'message' => 'Failed to search YouTube']);
        exit;
    }
    
    // Format results
    $videos = [];
    foreach ($search_results['items'] as $video) {
        $videos[] = [
            'video_id' => $video['id']['videoId'],
            'title' => $video['snippet']['title'],
            'description' => $video['snippet']['description'],
            'thumbnail_url' => $video['snippet']['thumbnails']['medium']['url'] ?? '',
            'channel_title' => $video['snippet']['channelTitle'],
            'published_at' => $video['snippet']['publishedAt']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'videos' => $videos
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Search failed']);
}
?>
