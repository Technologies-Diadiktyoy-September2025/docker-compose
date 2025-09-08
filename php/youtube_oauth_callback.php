<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/youtube_config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = null;
$success = false;

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange code for token
    $tokenData = exchangeCodeForToken($code);
    
    if ($tokenData && isset($tokenData['access_token'])) {
        try {
            // Calculate token expiration time
            $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
            
            // Store or update credentials
            $stmt = $pdo->prepare('
                INSERT INTO youtube_credentials (user_id, access_token, refresh_token, expires_at) 
                VALUES (:user_id, :access_token, :refresh_token, :expires_at)
                ON DUPLICATE KEY UPDATE 
                access_token = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                expires_at = VALUES(expires_at),
                updated_at = CURRENT_TIMESTAMP
            ');
            
            $stmt->execute([
                ':user_id' => $user_id,
                ':access_token' => $tokenData['access_token'],
                ':refresh_token' => $tokenData['refresh_token'] ?? null,
                ':expires_at' => $expiresAt
            ]);
            
            $success = true;
            
        } catch (PDOException $e) {
            $error = 'Failed to save YouTube credentials.';
        }
    } else {
        $error = 'Failed to obtain YouTube access token.';
    }
} elseif (isset($_GET['error'])) {
    $error = 'YouTube authorization was denied or failed.';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>YouTube Authorization - My Site</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site</span></div>
      <nav>
        <a href="index.html">Home</a>
        <a href="profile.php">Profile</a>
        <a href="my_lists.php">My Lists</a>
        <a href="logout.php">Logout</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>YouTube Authorization</h1>

    <?php if ($success): ?>
      <div class="card" style="padding:24px; text-align:center;">
        <h2 style="color:#43a047; margin-top:0;">✅ Authorization Successful!</h2>
        <p>You have successfully connected your YouTube account.</p>
        <p>You can now search and add YouTube videos to your lists.</p>
        <div style="margin-top:24px;">
          <a href="my_lists.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px; margin-right:12px;">Go to My Lists</a>
          <a href="search_content.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Search Videos</a>
        </div>
      </div>
    <?php elseif ($error): ?>
      <div class="card" style="padding:24px; text-align:center;">
        <h2 style="color:#e53935; margin-top:0;">❌ Authorization Failed</h2>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <div style="margin-top:24px;">
          <a href="my_lists.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Back to My Lists</a>
        </div>
      </div>
    <?php else: ?>
      <div class="card" style="padding:24px; text-align:center;">
        <h2>Processing Authorization...</h2>
        <p>Please wait while we process your YouTube authorization.</p>
      </div>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
