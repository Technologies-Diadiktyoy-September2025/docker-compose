<?php
/**
 * Router for PHP built-in server to handle SPA and API routes
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($uri, '/');

// Handle API routes
if (strpos($path, 'api/') === 0) {
    $apiFile = __DIR__ . '/' . $path . '.php';
    if (file_exists($apiFile)) {
        require $apiFile;
        return true;
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
        return true;
    }
}

// Handle SPA routes
if ($path === '' || $path === 'spa' || $path === 'spa.php') {
    require __DIR__ . '/spa.php';
    return true;
}

// Handle other PHP files
if (file_exists(__DIR__ . '/' . $path . '.php')) {
    require __DIR__ . '/' . $path . '.php';
    return true;
}

// Handle static files
if (file_exists(__DIR__ . '/' . $path)) {
    return false; // Let PHP built-in server handle static files
}

// Default to spa.php for SPA routes
if (in_array($path, ['home', 'lists', 'search', 'profile', 'list-view', 'video-player'])) {
    require __DIR__ . '/spa.php';
    return true;
}

// 404 for everything else
http_response_code(404);
echo "Page not found: " . htmlspecialchars($path);
?>
