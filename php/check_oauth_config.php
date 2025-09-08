<?php
require_once __DIR__ . '/youtube_config.php';

echo "<h1>OAuth Configuration Check</h1>";

echo "<h2>Current Configuration:</h2>";
echo "<p><strong>Client ID:</strong> " . YOUTUBE_CLIENT_ID . "</p>";
echo "<p><strong>Redirect URI:</strong> " . YOUTUBE_REDIRECT_URI . "</p>";
echo "<p><strong>API Key:</strong> " . YOUTUBE_API_KEY . "</p>";

echo "<h2>OAuth URL:</h2>";
$oauth_url = getYouTubeOAuthUrl();
echo "<p><a href='" . htmlspecialchars($oauth_url) . "' target='_blank'>Test OAuth URL</a></p>";

echo "<h2>What to do next:</h2>";
echo "<ol>";
echo "<li>Click the OAuth URL above</li>";
echo "<li>If you get an error, note what it says</li>";
echo "<li>Go back to Google Cloud Console</li>";
echo "<li>Look for 'Scopes' and 'Test Users' sections</li>";
echo "</ol>";
?>
