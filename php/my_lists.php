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
$message = null;
$messageType = null;

// Handle list creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_list') {
    $list_name = trim($_POST['list_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    
    if ($list_name === '') {
        $message = 'List name is required.';
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO user_lists (user_id, list_name, description, is_public) VALUES (:user_id, :list_name, :description, :is_public)');
            $stmt->execute([
                ':user_id' => $user_id,
                ':list_name' => $list_name,
                ':description' => $description,
                ':is_public' => $is_public
            ]);
            $message = 'List created successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Failed to create list.';
            $messageType = 'error';
        }
    }
}

// Get user's lists
$stmt = $pdo->prepare('
    SELECT ul.*, 
           COUNT(li.id) as item_count,
           u.username
    FROM user_lists ul
    LEFT JOIN list_items li ON ul.id = li.list_id
    LEFT JOIN users u ON ul.user_id = u.id
    WHERE ul.user_id = :user_id
    GROUP BY ul.id
    ORDER BY ul.updated_at DESC
');
$stmt->execute([':user_id' => $user_id]);
$user_lists = $stmt->fetchAll();

// Get followed users' public lists
$stmt = $pdo->prepare('
    SELECT ul.*, 
           COUNT(li.id) as item_count,
           u.username
    FROM user_lists ul
    LEFT JOIN list_items li ON ul.id = li.list_id
    LEFT JOIN users u ON ul.user_id = u.id
    INNER JOIN user_follows uf ON ul.user_id = uf.following_id
    WHERE uf.follower_id = :user_id AND ul.is_public = 1
    GROUP BY ul.id
    ORDER BY ul.updated_at DESC
');
$stmt->execute([':user_id' => $user_id]);
$followed_lists = $stmt->fetchAll();

// Check YouTube credentials
$has_youtube_auth = hasValidYouTubeCredentials($user_id, $pdo);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Lists - My Site</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site</span></div>
      <nav>
        <a href="index.html">Home</a>
        <a href="profile.php">Profile</a>
        <a href="my_lists.php" aria-current="page">My Lists</a>
        <a href="logout.php">Logout</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>My Lists</h1>
    <p class="muted">Manage your streaming content lists and discover content from users you follow</p>

    <?php if ($message): ?>
      <div class="card" style="padding:12px; border-left:4px solid <?php echo $messageType === 'success' ? '#43a047' : '#e53935'; ?>;">
        <strong><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <br>
    <?php endif; ?>

    <!-- YouTube Authorization Status -->
    <?php if (!$has_youtube_auth): ?>
      <div class="card" style="padding:16px; border-left:4px solid #ff9800;">
        <h3 style="margin-top:0;">🔗 Connect YouTube Account</h3>
        <p>To search and add YouTube videos to your lists, you need to connect your YouTube account.</p>
        <a href="<?php echo getYouTubeOAuthUrl(); ?>" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Connect YouTube</a>
      </div>
      <br>
    <?php endif; ?>

    <!-- Create New List -->
    <div class="card" style="padding:24px;">
      <h2 style="margin-top:0;">Create New List</h2>
      <form method="post" action="my_lists.php" style="display:grid; gap:12px;">
        <input type="hidden" name="action" value="create_list">
        
        <div>
          <label for="list_name">List Name *</label><br>
          <input type="text" id="list_name" name="list_name" required style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
        </div>
        
        <div>
          <label for="description">Description</label><br>
          <textarea id="description" name="description" rows="3" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px; resize:vertical;"></textarea>
        </div>
        
        <div>
          <label style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="is_public" value="1">
            <span>Make this list public (visible to other users)</span>
          </label>
        </div>
        
        <div>
          <button type="submit" class="theme-toggle" style="font-weight:600;">Create List</button>
        </div>
      </form>
    </div>

    <!-- My Lists -->
    <div class="card" style="padding:24px; margin-top:16px;">
      <h2 style="margin-top:0;">My Lists (<?php echo count($user_lists); ?>)</h2>
      
      <?php if (empty($user_lists)): ?>
        <p class="muted">You haven't created any lists yet. Create your first list above!</p>
      <?php else: ?>
        <div style="display:grid; gap:16px;">
          <?php foreach ($user_lists as $list): ?>
            <div style="padding:16px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:8px;">
              <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">
                <div>
                  <h3 style="margin:0 0 4px 0;">
                    <a href="view_list.php?id=<?php echo $list['id']; ?>" style="color:var(--accent); text-decoration:none;">
                      <?php echo htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  </h3>
                  <p style="margin:0; color:var(--muted-text); font-size:0.9rem;">
                    <?php echo $list['item_count']; ?> items • 
                    <?php echo $list['is_public'] ? 'Public' : 'Private'; ?> • 
                    Updated <?php echo date('M j, Y', strtotime($list['updated_at'])); ?>
                  </p>
                </div>
                <div style="display:flex; gap:8px;">
                  <a href="edit_list.php?id=<?php echo $list['id']; ?>" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:4px 8px; font-size:0.9rem;">Edit</a>
                  <a href="add_content.php?list_id=<?php echo $list['id']; ?>" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:4px 8px; font-size:0.9rem;">Add Content</a>
                </div>
              </div>
              <?php if ($list['description']): ?>
                <p style="margin:0; color:var(--muted-text);"><?php echo htmlspecialchars($list['description'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Followed Users' Lists -->
    <?php if (!empty($followed_lists)): ?>
      <div class="card" style="padding:24px; margin-top:16px;">
        <h2 style="margin-top:0;">Lists from Users You Follow (<?php echo count($followed_lists); ?>)</h2>
        
        <div style="display:grid; gap:16px;">
          <?php foreach ($followed_lists as $list): ?>
            <div style="padding:16px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:8px;">
              <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">
                <div>
                  <h3 style="margin:0 0 4px 0;">
                    <a href="view_list.php?id=<?php echo $list['id']; ?>" style="color:var(--accent); text-decoration:none;">
                      <?php echo htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  </h3>
                  <p style="margin:0; color:var(--muted-text); font-size:0.9rem;">
                    by <?php echo htmlspecialchars($list['username'], ENT_QUOTES, 'UTF-8'); ?> • 
                    <?php echo $list['item_count']; ?> items • 
                    Updated <?php echo date('M j, Y', strtotime($list['updated_at'])); ?>
                  </p>
                </div>
              </div>
              <?php if ($list['description']): ?>
                <p style="margin:0; color:var(--muted-text);"><?php echo htmlspecialchars($list['description'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
