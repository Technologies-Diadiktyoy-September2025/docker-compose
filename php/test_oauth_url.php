<?php
require_once __DIR__ . '/youtube_config.php';

echo "<h1>OAuth URL Test</h1>";
echo "<p><strong>Client ID:</strong> " . YOUTUBE_CLIENT_ID . "</p>";
echo "<p><strong>Redirect URI:</strong> " . YOUTUBE_REDIRECT_URI . "</p>";

$oauth_url = getYouTubeOAuthUrl();
echo "<p><strong>Generated OAuth URL:</strong></p>";
echo "<p><a href='" . htmlspecialchars($oauth_url) . "' target='_blank'>" . htmlspecialchars($oauth_url) . "</a></p>";

echo "<h2>Test the OAuth URL</h2>";
echo "<p>Click the link above to test if the OAuth URL works correctly.</p>";
?>
