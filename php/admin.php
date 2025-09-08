<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Simple admin check - in a real application, you'd have proper role-based access
// For now, we'll just check if the user is logged in
requireLogin();

$user_id = getCurrentUserId();

// Get all users (excluding password hashes for security)
$stmt = $pdo->prepare('SELECT id, first_name, last_name, username, email, created_at FROM users ORDER BY created_at DESC');
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - User Management</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site - Admin</span></div>
      <nav>
        <a href="index.html">Home</a>
        <a href="profile.php">Profile</a>
        <a href="admin.php" aria-current="page">Admin</a>
        <a href="logout.php">Logout</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>User Management</h1>
    <p class="muted">View and manage registered users</p>

    <div class="card" style="padding:24px;">
      <h2 style="margin-top:0;">Registered Users (<?php echo count($users); ?>)</h2>
      
      <?php if (empty($users)): ?>
        <p>No users found.</p>
      <?php else: ?>
        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; margin-top:16px;">
            <thead>
              <tr style="border-bottom:2px solid var(--border-color);">
                <th style="text-align:left; padding:12px 8px;">ID</th>
                <th style="text-align:left; padding:12px 8px;">Name</th>
                <th style="text-align:left; padding:12px 8px;">Username</th>
                <th style="text-align:left; padding:12px 8px;">Email</th>
                <th style="text-align:left; padding:12px 8px;">Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                  <td style="padding:12px 8px;"><?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="padding:12px 8px;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="padding:12px 8px;"><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="padding:12px 8px;"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="padding:12px 8px;"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card" style="padding:24px; margin-top:16px;">
      <h3 style="margin-top:0;">Database Information</h3>
      <p><strong>Total Users:</strong> <?php echo count($users); ?></p>
      <p><strong>Database:</strong> <?php echo htmlspecialchars($GLOBALS['DB_NAME'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></p>
      <p><strong>Current User:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>

