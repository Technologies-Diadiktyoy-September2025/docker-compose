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
$list_id = $_GET['list_id'] ?? null;
$list = null;
$error = null;

// Check YouTube credentials
if (!hasValidYouTubeCredentials($user_id, $pdo)) {
    header('Location: my_lists.php');
    exit;
}

if (!$list_id || !is_numeric($list_id)) {
    $error = 'Invalid list ID.';
} else {
    // Get list information
    $stmt = $pdo->prepare('SELECT * FROM user_lists WHERE id = :list_id AND user_id = :user_id');
    $stmt->execute([':list_id' => $list_id, ':user_id' => $user_id]);
    $list = $stmt->fetch();
    
    if (!$list) {
        $error = 'List not found or you do not have permission to edit it.';
    }
}

// Redirect to search if no error
if (!$error) {
    header('Location: search_content.php?list_id=' . $list_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Content - My Site</title>
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
    <?php if ($error): ?>
      <div class="card" style="padding:24px; text-align:center;">
        <h1 style="color:#e53935;">❌ Error</h1>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <a href="my_lists.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Back to My Lists</a>
      </div>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
