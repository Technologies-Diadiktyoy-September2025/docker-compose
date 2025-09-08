<?php
session_start();
require_once __DIR__ . '/db.php';

// Simple test page to verify SPA setup
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SPA Test - My Site</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site - SPA Test</span></div>
      <nav>
        <a href="index.html">Home</a>
        <a href="spa.php">SPA</a>
        <a href="login.php">Login</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>SPA Setup Test</h1>
    
    <div class="card" style="padding: 24px;">
      <h2>System Status</h2>
      
      <div style="display: grid; gap: 16px;">
        <div>
          <strong>PHP Version:</strong> <?php echo phpversion(); ?>
        </div>
        
        <div>
          <strong>Session Status:</strong> 
          <?php if (isset($_SESSION['user_id'])): ?>
            <span style="color: green;">✅ Logged in as <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
          <?php else: ?>
            <span style="color: orange;">⚠️ Not logged in</span>
          <?php endif; ?>
        </div>
        
        <div>
          <strong>Database Connection:</strong>
          <?php
          try {
            $pdo->query('SELECT 1');
            echo '<span style="color: green;">✅ Connected</span>';
          } catch (Exception $e) {
            echo '<span style="color: red;">❌ Failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</span>';
          }
          ?>
        </div>
        
        <div>
          <strong>API Endpoints:</strong>
          <ul>
            <li><a href="api/lists.php" target="_blank">api/lists.php</a></li>
            <li><a href="api/create_list.php" target="_blank">api/create_list.php</a></li>
            <li><a href="api/search_videos.php" target="_blank">api/search_videos.php</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="card" style="padding: 24px; margin-top: 16px;">
      <h2>Quick Actions</h2>
      
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="spa.php" class="theme-toggle" style="text-decoration: none; font-weight: 600; padding: 12px 24px; background: var(--accent); color: white; border-radius: 8px;">🎬 Open SPA</a>
          <a href="test_create_list.php" class="theme-toggle" style="text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 8px;">🧪 Test Create List</a>
          <a href="profile.php" class="theme-toggle" style="text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 8px;">👤 Profile</a>
        <?php else: ?>
          <a href="login.php" class="theme-toggle" style="text-decoration: none; font-weight: 600; padding: 12px 24px; background: var(--accent); color: white; border-radius: 8px;">🔐 Login</a>
          <a href="register.php" class="theme-toggle" style="text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 8px;">📝 Register</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="card" style="padding: 24px; margin-top: 16px;">
      <h2>Access URLs</h2>
      
      <div style="display: grid; gap: 8px;">
        <div><strong>XAMPP Apache:</strong> <code>http://localhost/rigas-ergasia/spa.php</code></div>
        <div><strong>PHP Built-in Server:</strong> <code>http://localhost:8000/spa.php</code></div>
        <div><strong>Test Page:</strong> <code>http://localhost/rigas-ergasia/test_spa.php</code></div>
      </div>
    </div>
  </main>

  <script src="script.js"></script>
</body>
</html>
