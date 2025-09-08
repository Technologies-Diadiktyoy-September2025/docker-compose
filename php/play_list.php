<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$list_id = $_GET['id'] ?? null;
$list = null;
$list_items = [];
$error = null;

if (!$list_id || !is_numeric($list_id)) {
    $error = 'Invalid list ID.';
} else {
    // Get list information
    $stmt = $pdo->prepare('
        SELECT ul.*, u.username
        FROM user_lists ul
        LEFT JOIN users u ON ul.user_id = u.id
        WHERE ul.id = :list_id
    ');
    $stmt->execute([':list_id' => $list_id]);
    $list = $stmt->fetch();
    
    if (!$list) {
        $error = 'List not found.';
    } else {
        // Check if user can view this list
        if ($list['user_id'] != $user_id && !$list['is_public']) {
            $error = 'You do not have permission to view this list.';
        } else {
            // Get list items
            $stmt = $pdo->prepare('
                SELECT sc.*, li.position_order
                FROM list_items li
                INNER JOIN streaming_content sc ON li.content_id = sc.id
                WHERE li.list_id = :list_id
                ORDER BY li.position_order ASC, li.added_at ASC
            ');
            $stmt->execute([':list_id' => $list_id]);
            $list_items = $stmt->fetchAll();
            
            if (empty($list_items)) {
                $error = 'This list is empty.';
            }
        }
    }
}

// Redirect to first video if list has items
if (!$error && !empty($list_items)) {
    header('Location: play_video.php?id=' . $list_items[0]['id'] . '&list_id=' . $list_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Play List - My Site</title>
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
    <?php else: ?>
      <div class="card" style="padding:24px; text-align:center;">
        <h1>Starting Playlist...</h1>
        <p>Redirecting to the first video in the playlist.</p>
      </div>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
