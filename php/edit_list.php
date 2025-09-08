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
$error = null;
$success = false;

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $list) {
    $list_name = trim($_POST['list_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    
    if ($list_name === '') {
        $error = 'List name is required.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE user_lists SET list_name = :list_name, description = :description, is_public = :is_public WHERE id = :list_id AND user_id = :user_id');
            $stmt->execute([
                ':list_name' => $list_name,
                ':description' => $description,
                ':is_public' => $is_public,
                ':list_id' => $list_id,
                ':user_id' => $user_id
            ]);
            $success = true;
            $list['list_name'] = $list_name;
            $list['description'] = $description;
            $list['is_public'] = $is_public;
        } catch (PDOException $e) {
            $error = 'Failed to update list.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit List - My Site</title>
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
    <h1>Edit List</h1>

    <?php if ($error): ?>
      <div class="card" style="padding:12px; border-left:4px solid #e53935;">
        <strong><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <br>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="card" style="padding:12px; border-left:4px solid #43a047;">
        <strong>List updated successfully!</strong>
        <p style="margin:8px 0 0 0;"><a href="view_list.php?id=<?php echo $list_id; ?>" style="color:var(--accent);">View List</a></p>
      </div>
      <br>
    <?php endif; ?>

    <?php if ($list): ?>
      <form class="card" method="post" action="edit_list.php?id=<?php echo $list_id; ?>" style="padding:16px; display:grid; gap:12px;">
        <div>
          <label for="list_name">List Name *</label><br>
          <input type="text" id="list_name" name="list_name" required 
                 value="<?php echo htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?>"
                 style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
        </div>
        
        <div>
          <label for="description">Description</label><br>
          <textarea id="description" name="description" rows="3" 
                    style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px; resize:vertical;"><?php echo htmlspecialchars($list['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        
        <div>
          <label style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="is_public" value="1" <?php echo $list['is_public'] ? 'checked' : ''; ?>>
            <span>Make this list public (visible to other users)</span>
          </label>
        </div>
        
        <div style="display:flex; gap:12px; margin-top:16px;">
          <button type="submit" class="theme-toggle" style="font-weight:600;">Update List</button>
          <a href="view_list.php?id=<?php echo $list_id; ?>" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px; text-align:center;">Cancel</a>
        </div>
      </form>
    <?php else: ?>
      <div class="card" style="padding:24px; text-align:center;">
        <h2 style="color:#e53935;">List Not Found</h2>
        <p>The list you're trying to edit doesn't exist or you don't have permission to edit it.</p>
        <a href="my_lists.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Back to My Lists</a>
      </div>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
