<?php
/**
 * YouTube API Configuration
 * 
 * To use this system, you need to:
 * 1. Go to Google Cloud Console (https://console.cloud.google.com/)
 * 2. Create a new project or select existing one
 * 3. Enable YouTube Data API v3
 * 4. Create OAuth 2.0 credentials
 * 5. Set authorized redirect URIs to include your domain
 * 6. Update the credentials below
 */

// YouTube API Configuration
// TODO: Replace these with your actual Google Cloud Console credentials
define('YOUTUBE_CLIENT_ID', '728349338261-uvul66pi4j8os8ir0ebn8csvg0etqiku.apps.googleusercontent.com');
define('YOUTUBE_CLIENT_SECRET', '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');
define('YOUTUBE_REDIRECT_URI', 'http://localhost/rigas-ergasia/youtube_oauth_callback.php');
define('YOUTUBE_API_KEY', 'AIzaSyD6awV9sQyV9OL7Wj0MpoL5eaJF7qeFw04');

// YouTube API endpoints
define('YOUTUBE_OAUTH_URL', 'https://accounts.google.com/o/oauth2/auth');
define('YOUTUBE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('YOUTUBE_API_BASE', 'https://www.googleapis.com/youtube/v3');

// Scopes for YouTube API access
define('YOUTUBE_SCOPES', [
    'https://www.googleapis.com/auth/youtube.readonly',
    'https://www.googleapis.com/auth/youtube'
]);

/**
 * Generate YouTube OAuth URL
 */
function getYouTubeOAuthUrl() {
    $params = [
        'client_id' => YOUTUBE_CLIENT_ID,
        'redirect_uri' => YOUTUBE_REDIRECT_URI,
        'scope' => implode(' ', YOUTUBE_SCOPES),
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    return YOUTUBE_OAUTH_URL . '?' . http_build_query($params);
}

/**
 * Exchange authorization code for access token
 */
function exchangeCodeForToken($code) {
    $data = [
        'client_id' => YOUTUBE_CLIENT_ID,
        'client_secret' => YOUTUBE_CLIENT_SECRET,
        'redirect_uri' => YOUTUBE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
        'code' => $code
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, YOUTUBE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

/**
 * Refresh access token
 */
function refreshAccessToken($refreshToken) {
    $data = [
        'client_id' => YOUTUBE_CLIENT_ID,
        'client_secret' => YOUTUBE_CLIENT_SECRET,
        'refresh_token' => $refreshToken,
        'grant_type' => 'refresh_token'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, YOUTUBE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

/**
 * Search YouTube videos
 */
function searchYouTubeVideos($query, $accessToken, $maxResults = 10) {
    $params = [
        'part' => 'snippet',
        'q' => $query,
        'type' => 'video',
        'maxResults' => $maxResults,
        'order' => 'relevance'
    ];
    
    $url = YOUTUBE_API_BASE . '/search?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

/**
 * Get video details
 */
function getYouTubeVideoDetails($videoId, $accessToken) {
    $params = [
        'part' => 'snippet,contentDetails,statistics',
        'id' => $videoId
    ];
    
    $url = YOUTUBE_API_BASE . '/videos?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

/**
 * Check if user has valid YouTube credentials
 */
function hasValidYouTubeCredentials($userId, $pdo) {
    $stmt = $pdo->prepare('SELECT * FROM youtube_credentials WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    $credentials = $stmt->fetch();
    
    if (!$credentials) {
        return false;
    }
    
    // Check if token is expired
    if ($credentials['expires_at'] && strtotime($credentials['expires_at']) < time()) {
        // Try to refresh token
        if ($credentials['refresh_token']) {
            $newToken = refreshAccessToken($credentials['refresh_token']);
            if ($newToken) {
                // Update credentials
                $expiresAt = date('Y-m-d H:i:s', time() + $newToken['expires_in']);
                $stmt = $pdo->prepare('UPDATE youtube_credentials SET access_token = :access_token, expires_at = :expires_at WHERE user_id = :user_id');
                $stmt->execute([
                    ':access_token' => $newToken['access_token'],
                    ':expires_at' => $expiresAt,
                    ':user_id' => $userId
                ]);
                return true;
            }
        }
        return false;
    }
    
    return true;
}
?>
