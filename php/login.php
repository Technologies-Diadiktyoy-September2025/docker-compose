<?php
session_start();
require_once __DIR__ . '/db.php';

$errors = [];
$success = false;

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') { $errors[] = 'Username is required.'; }
    if ($password === '') { $errors[] = 'Password is required.'; }

    if (empty($errors)) {
        // Check user credentials
        $stmt = $pdo->prepare('SELECT id, username, password_hash, first_name, last_name, email FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            header('Location: profile.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - My Site</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site</span></div>
      <nav>
        <a href="index.html">Home</a>
        <a href="help.html">Help</a>
        <a href="register.php">Register</a>
        <a href="login.php" aria-current="page">Login</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>Sign In</h1>
    <p class="muted">Enter your credentials to access your account</p>

    <?php if (!empty($errors)): ?>
      <div class="card" style="padding:12px; border-left:4px solid #e53935;">
        <strong>There were problems with your submission:</strong>
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <br>
    <?php endif; ?>

    <form class="card" method="post" action="login.php" style="padding:16px; display:grid; gap:12px;">
      <div>
        <label for="username">Username</label><br>
        <input type="text" id="username" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : '';?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      <div>
        <label for="password">Password</label><br>
        <input type="password" id="password" name="password" required style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      <div>
        <button type="submit" class="theme-toggle" style="font-weight:600;">Sign In</button>
      </div>
    </form>

    <div class="card" style="padding:16px; text-align:center; margin-top:16px;">
      <p style="margin:0;">Don't have an account? <a href="register.php" style="color:var(--accent);">Create one here</a></p>
    </div>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>

