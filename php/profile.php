<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user information from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$email = $_SESSION['email'];

// Get additional user info from database
$stmt = $pdo->prepare('SELECT created_at FROM users WHERE id = :user_id');
$stmt->execute([':user_id' => $user_id]);
$user_info = $stmt->fetch();

$created_at = $user_info['created_at'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile - My Site</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site</span></div>
      <nav>
        <a href="index.html">Home</a>
        <a href="help.html">Help</a>
        <a href="profile.php" aria-current="page">Profile</a>
        <a href="my_lists.php">My Lists</a>
        <a href="logout.php">Logout</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>My Profile</h1>
    <p class="muted">Manage your account information and settings</p>

    <div class="card" style="padding:24px;">
      <h2 style="margin-top:0;">Account Information</h2>
      
      <div style="display:grid; gap:16px;">
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:12px; align-items:center;">
          <strong>Name:</strong>
          <span><?php echo htmlspecialchars($first_name . ' ' . $last_name, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:12px; align-items:center;">
          <strong>Username:</strong>
          <span><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:12px; align-items:center;">
          <strong>Email:</strong>
          <span><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:12px; align-items:center;">
          <strong>Member Since:</strong>
          <span><?php echo $created_at ? date('F j, Y', strtotime($created_at)) : 'Unknown'; ?></span>
        </div>
      </div>

      <div style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
        <a href="spa.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px; background:var(--accent); color:white;">🎬 Streaming Manager (SPA)</a>
        <a href="edit_profile.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Edit Profile</a>
        <a href="admin.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Admin Panel</a>
        <a href="delete_profile.php" style="background:#e53935; color:white; text-decoration:none; font-weight:600; padding:8px 16px; border-radius:8px; border:none;">Delete Account</a>
      </div>
    </div>

    <div class="card" style="padding:24px; margin-top:16px;">
      <h3 style="margin-top:0;">Account Actions</h3>
      <p>Here you can manage your account:</p>
      <ul>
        <li><strong>Streaming Manager (SPA):</strong> Access the modern single-page application for managing your YouTube playlists</li>
        <li><strong>Edit Profile:</strong> Update your personal information, username, or email</li>
        <li><strong>Delete Account:</strong> Permanently remove your account and all associated data</li>
      </ul>
      
      <div style="margin-top:16px; padding:16px; background:rgba(255, 193, 7, 0.1); border-left:4px solid #ffc107; border-radius:4px;">
        <strong>⚠️ Warning:</strong> Deleting your account is permanent and cannot be undone. All your data, including any lists you have created, will be permanently removed.
      </div>
    </div>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
