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
        SELECT ul.*, u.username, u.first_name, u.last_name
        FROM user_lists ul
        LEFT JOIN users u ON ul.user_id = u.id
        WHERE ul.id = :list_id
    ');
    $stmt->execute([':list_id' => $list_id]);
    $list = $stmt->fetch();
    
    if (!$list) {
        $error = 'List not found.';
    } else {
        // Check if user can view this list (owner or public)
        if ($list['user_id'] != $user_id && !$list['is_public']) {
            $error = 'You do not have permission to view this list.';
        } else {
            // Get list items
            $stmt = $pdo->prepare('
                SELECT sc.*, li.position_order, li.added_at as added_to_list_at
                FROM list_items li
                INNER JOIN streaming_content sc ON li.content_id = sc.id
                WHERE li.list_id = :list_id
                ORDER BY li.position_order ASC, li.added_at ASC
            ');
            $stmt->execute([':list_id' => $list_id]);
            $list_items = $stmt->fetchAll();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $list ? htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8') : 'List Not Found'; ?> - My Site</title>
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
      <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:24px;">
        <div>
          <h1 style="margin:0 0 8px 0;"><?php echo htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
          <p class="muted" style="margin:0;">
            by <?php echo htmlspecialchars($list['first_name'] . ' ' . $list['last_name'], ENT_QUOTES, 'UTF-8'); ?> 
            (<?php echo htmlspecialchars($list['username'], ENT_QUOTES, 'UTF-8'); ?>) • 
            <?php echo count($list_items); ?> items • 
            <?php echo $list['is_public'] ? 'Public' : 'Private'; ?>
          </p>
        </div>
        
        <div style="display:flex; gap:8px;">
          <?php if ($list['user_id'] == $user_id): ?>
            <a href="edit_list.php?id=<?php echo $list['id']; ?>" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Edit List</a>
            <a href="add_content.php?list_id=<?php echo $list['id']; ?>" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Add Content</a>
          <?php endif; ?>
          <a href="play_list.php?id=<?php echo $list['id']; ?>" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px; background:var(--accent); color:white;">Play All</a>
        </div>
      </div>

      <?php if ($list['description']): ?>
        <div class="card" style="padding:16px; margin-bottom:16px;">
          <p style="margin:0;"><?php echo htmlspecialchars($list['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
      <?php endif; ?>

      <!-- List Items -->
      <div class="card" style="padding:24px;">
        <h2 style="margin-top:0;">Videos (<?php echo count($list_items); ?>)</h2>
        
        <?php if (empty($list_items)): ?>
          <p class="muted">This list is empty. <?php echo $list['user_id'] == $user_id ? 'Add some videos to get started!' : 'The owner hasn\'t added any videos yet.'; ?></p>
        <?php else: ?>
          <div style="display:grid; gap:16px;">
            <?php foreach ($list_items as $index => $item): ?>
              <div style="display:flex; gap:16px; padding:16px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:8px;">
                <div style="flex-shrink:0; position:relative;">
                  <img src="<?php echo htmlspecialchars($item['thumbnail_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                       alt="Video thumbnail" style="width:160px; height:120px; object-fit:cover; border-radius:4px;">
                  <div style="position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.8); color:white; padding:2px 6px; border-radius:2px; font-size:0.8rem;">
                    <?php echo $index + 1; ?>
                  </div>
                </div>
                
                <div style="flex:1;">
                  <h3 style="margin:0 0 8px 0; font-size:1.1rem;">
                    <a href="play_video.php?id=<?php echo $item['id']; ?>&list_id=<?php echo $list['id']; ?>" 
                       style="color:var(--accent); text-decoration:none;">
                      <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  </h3>
                  
                  <p style="margin:0 0 8px 0; color:var(--muted-text); font-size:0.9rem;">
                    by <?php echo htmlspecialchars($item['channel_title'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php if ($item['published_at']): ?>
                      • Published <?php echo date('M j, Y', strtotime($item['published_at'])); ?>
                    <?php endif; ?>
                  </p>
                  
                  <p style="margin:0 0 8px 0; color:var(--muted-text); font-size:0.9rem;">
                    Added to list <?php echo date('M j, Y', strtotime($item['added_to_list_at'])); ?>
                  </p>
                  
                  <?php if ($item['description']): ?>
                    <p style="margin:0; color:var(--muted-text); font-size:0.9rem; line-height:1.4;">
                      <?php echo htmlspecialchars(substr($item['description'], 0, 200) . (strlen($item['description']) > 200 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                  <?php endif; ?>
                  
                  <div style="margin-top:12px; display:flex; gap:8px;">
                    <a href="play_video.php?id=<?php echo $item['id']; ?>&list_id=<?php echo $list['id']; ?>" 
                       class="theme-toggle" style="text-decoration:none; font-weight:600; padding:6px 12px; font-size:0.9rem;">Play</a>
                    <?php if ($list['user_id'] == $user_id): ?>
                      <a href="remove_from_list.php?item_id=<?php echo $item['id']; ?>&list_id=<?php echo $list['id']; ?>" 
                         style="background:#e53935; color:white; text-decoration:none; font-weight:600; padding:6px 12px; font-size:0.9rem; border-radius:4px;"
                         onclick="return confirm('Remove this video from the list?')">Remove</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
