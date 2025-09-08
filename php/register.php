<?php
require_once __DIR__ . '/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $email     = trim($_POST['email'] ?? '');

    if ($firstName === '') { $errors[] = 'First name is required.'; }
    if ($lastName === '')  { $errors[] = 'Last name is required.'; }
    if ($username === '')  { $errors[] = 'Username is required.'; }
    if ($password === '')  { $errors[] = 'Password is required.'; }
    if ($email === '')     { $errors[] = 'Email is required.'; }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email format is invalid.';
    }

    if (empty($errors)) {
        // Check uniqueness for username and email
        $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE username = :username OR email = :email');
        $stmt->execute([':username' => $username, ':email' => $email]);
        $row = $stmt->fetch();
        if ($row && (int)$row['cnt'] > 0) {
            $errors[] = 'Username or Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, username, password_hash, email, created_at) VALUES (:first_name, :last_name, :username, :password_hash, :email, NOW())');
            try {
                $stmt->execute([
                    ':first_name' => $firstName,
                    ':last_name'  => $lastName,
                    ':username'   => $username,
                    ':password_hash' => $hash,
                    ':email'      => $email,
                ]);
                $success = true;
            } catch (PDOException $e) {
                $errors[] = 'Failed to save user.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site</span></div>
      <nav>
        <a href="index.html">Home</a>
        <a href="help.html">Help</a>
        <a href="register.php" aria-current="page">Register</a>
        <a href="login.php">Login</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>Create your account</h1>

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

    <?php if ($success): ?>
      <div class="card" style="padding:12px; border-left:4px solid #43a047;">
        <strong>Registration successful.</strong> You can now sign in.
      </div>
    <?php else: ?>
    <form class="card" method="post" action="register.php" style="padding:16px; display:grid; gap:12px;">
      <div>
        <label for="first_name">First name</label><br>
        <input type="text" id="first_name" name="first_name" required value="<?php echo isset($firstName) ? htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') : '';?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      <div>
        <label for="last_name">Last name</label><br>
        <input type="text" id="last_name" name="last_name" required value="<?php echo isset($lastName) ? htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') : '';?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      <div>
        <label for="username">Username</label><br>
        <input type="text" id="username" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : '';?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      <div>
        <label for="password">Password</label><br>
        <input type="password" id="password" name="password" required minlength="6" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      <div>
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '';?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      <div>
        <button type="submit" class="theme-toggle" style="font-weight:600;">Register</button>
      </div>
    </form>
    <?php endif; ?>
    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html> 