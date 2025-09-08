<?php
session_start();
require_once __DIR__ . '/../db.php';

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
$query = trim($input['query'] ?? '');

if ($query === '') {
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit;
}

// Mock YouTube search results for testing
$mockVideos = [
    [
        'video_id' => 'dQw4w9WgXcQ',
        'title' => 'Rick Astley - Never Gonna Give You Up (Official Video)',
        'description' => 'The official video for "Never Gonna Give You Up" by Rick Astley',
        'thumbnail_url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/mqdefault.jpg',
        'channel_title' => 'Rick Astley',
        'published_at' => '2009-10-25T06:57:33Z'
    ],
    [
        'video_id' => '9bZkp7q19f0',
        'title' => 'PSY - GANGNAM STYLE (강남스타일) M/V',
        'description' => 'PSY - GANGNAM STYLE (강남스타일) Official Music Video',
        'thumbnail_url' => 'https://img.youtube.com/vi/9bZkp7q19f0/mqdefault.jpg',
        'channel_title' => 'officialpsy',
        'published_at' => '2012-07-15T07:46:32Z'
    ],
    [
        'video_id' => 'kJQP7kiw5Fk',
        'title' => 'Luis Fonsi - Despacito ft. Daddy Yankee',
        'description' => 'Luis Fonsi - Despacito ft. Daddy Yankee',
        'thumbnail_url' => 'https://img.youtube.com/vi/kJQP7kiw5Fk/mqdefault.jpg',
        'channel_title' => 'Luis Fonsi',
        'published_at' => '2017-01-13T04:00:00Z'
    ],
    [
        'video_id' => 'YQHsXMglC9A',
        'title' => 'Adele - Hello',
        'description' => 'Adele - Hello (Official Video)',
        'thumbnail_url' => 'https://img.youtube.com/vi/YQHsXMglC9A/mqdefault.jpg',
        'channel_title' => 'Adele',
        'published_at' => '2015-10-23T07:00:00Z'
    ],
    [
        'video_id' => 'fJ9rUzIMcZQ',
        'title' => 'Queen - Bohemian Rhapsody (Official Video)',
        'description' => 'Queen - Bohemian Rhapsody (Official Video)',
        'thumbnail_url' => 'https://img.youtube.com/vi/fJ9rUzIMcZQ/mqdefault.jpg',
        'channel_title' => 'Queen Official',
        'published_at' => '2008-10-31T07:00:00Z'
    ]
];

// Filter results based on query (simple text matching)
$filteredVideos = array_filter($mockVideos, function($video) use ($query) {
    return stripos($video['title'], $query) !== false || 
           stripos($video['description'], $query) !== false ||
           stripos($video['channel_title'], $query) !== false;
});

// If no matches, return all videos
if (empty($filteredVideos)) {
    $filteredVideos = $mockVideos;
}

// Limit to 10 results
$filteredVideos = array_slice($filteredVideos, 0, 10);

echo json_encode([
    'success' => true,
    'videos' => array_values($filteredVideos)
]);
?>
