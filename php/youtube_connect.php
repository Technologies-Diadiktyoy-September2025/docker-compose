<?php
session_start();
require_once __DIR__ . '/youtube_config.php';
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if user already has YouTube credentials
$stmt = $pdo->prepare('SELECT * FROM youtube_credentials WHERE user_id = ?');
$stmt->execute([$user_id]);
$hasCredentials = $stmt->fetch();

// Handle disconnect request
if (isset($_GET['disconnect'])) {
    $stmt = $pdo->prepare('DELETE FROM youtube_credentials WHERE user_id = ?');
    $stmt->execute([$user_id]);
    header('Location: youtube_connect.php?disconnected=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YouTube Connection - My Site</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <div class="brand"><span>My Site</span></div>
            <nav>
                <a href="index.html">Home</a>
                <a href="help.html">Help</a>
                <a href="profile.php">Profile</a>
                <a href="spa.php">Streaming Manager</a>
            </nav>
            <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
        </div>
    </header>

    <main class="container">
        <h1>YouTube Connection</h1>
        
        <?php if (isset($_GET['disconnected'])): ?>
            <div class="success">
                <p>YouTube account disconnected successfully!</p>
            </div>
        <?php endif; ?>

        <?php if ($hasCredentials): ?>
            <div class="card" style="padding: 20px; margin: 20px 0;">
                <h2>✅ YouTube Connected</h2>
                <p>Your YouTube account is connected and ready to use for video search.</p>
                <p><strong>Connected on:</strong> <?= date('F j, Y \a\t g:i A', strtotime($hasCredentials['created_at'])) ?></p>
                
                <div style="margin-top: 20px;">
                    <a href="spa.php" class="btn btn-primary">Go to Streaming Manager</a>
                    <a href="youtube_connect.php?disconnect=1" class="btn btn-danger" onclick="return confirm('Are you sure you want to disconnect your YouTube account?')">Disconnect YouTube</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card" style="padding: 20px; margin: 20px 0;">
                <h2>Connect Your YouTube Account</h2>
                <p>To search for YouTube videos and add them to your lists, you need to connect your YouTube account.</p>
                
                <div style="margin: 20px 0;">
                    <h3>What this allows:</h3>
                    <ul>
                        <li>Search for YouTube videos</li>
                        <li>Add videos to your streaming lists</li>
                        <li>View video details and thumbnails</li>
                        <li>Play videos from your lists</li>
                    </ul>
                </div>
                
                <div style="margin-top: 30px;">
                    <?php if (YOUTUBE_CLIENT_ID === 'your_client_id_here.apps.googleusercontent.com'): ?>
                        <div class="error">
                            <h3>⚠️ YouTube API Not Configured</h3>
                            <p>The YouTube API credentials need to be set up first. Please:</p>
                            <ol>
                                <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                                <li>Create a project and enable YouTube Data API v3</li>
                                <li>Create OAuth 2.0 credentials</li>
                                <li>Update the credentials in <code>youtube_config.php</code></li>
                            </ol>
                        </div>
                    <?php else: ?>
                        <a href="<?= getYouTubeOAuthUrl() ?>" class="btn btn-primary" style="font-size: 1.1em; padding: 12px 24px;">
                            🔗 Connect YouTube Account
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card" style="padding: 20px; margin: 20px 0;">
            <h3>How It Works</h3>
            <ol>
                <li><strong>Connect:</strong> Authorize the app to access your YouTube account</li>
                <li><strong>Search:</strong> Use the search feature to find YouTube videos</li>
                <li><strong>Add:</strong> Add videos to your custom streaming lists</li>
                <li><strong>Manage:</strong> Organize and play videos from your lists</li>
            </ol>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>
