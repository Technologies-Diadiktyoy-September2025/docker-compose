<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get current user data
$stmt = $pdo->prepare('SELECT first_name, last_name, username, email FROM users WHERE id = :user_id');
$stmt->execute([':user_id' => $user_id]);
$current_user = $stmt->fetch();

if (!$current_user) {
    header('Location: login.php');
    exit;
}

// Initialize form data with current values
$firstName = $current_user['first_name'];
$lastName = $current_user['last_name'];
$username = $current_user['username'];
$email = $current_user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if ($firstName === '') { $errors[] = 'First name is required.'; }
    if ($lastName === '') { $errors[] = 'Last name is required.'; }
    if ($username === '') { $errors[] = 'Username is required.'; }
    if ($email === '') { $errors[] = 'Email is required.'; }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email format is invalid.';
    }

    // Check if password change is requested
    $password_change = !empty($new_password);
    if ($password_change) {
        if ($current_password === '') { $errors[] = 'Current password is required to change password.'; }
        if ($new_password !== $confirm_password) { $errors[] = 'New passwords do not match.'; }
        if (strlen($new_password) < 6) { $errors[] = 'New password must be at least 6 characters long.'; }
    }

    if (empty($errors)) {
        // Verify current password if changing password
        if ($password_change) {
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :user_id');
            $stmt->execute([':user_id' => $user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($current_password, $user['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            }
        }

        if (empty($errors)) {
            // Check uniqueness for username and email (excluding current user)
            $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE (username = :username OR email = :email) AND id != :user_id');
            $stmt->execute([
                ':username' => $username, 
                ':email' => $email,
                ':user_id' => $user_id
            ]);
            $row = $stmt->fetch();
            
            if ($row && (int)$row['cnt'] > 0) {
                $errors[] = 'Username or Email already exists.';
            } else {
                // Update user information
                if ($password_change) {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, username = :username, email = :email, password_hash = :password_hash WHERE id = :user_id');
                    $stmt->execute([
                        ':first_name' => $firstName,
                        ':last_name' => $lastName,
                        ':username' => $username,
                        ':email' => $email,
                        ':password_hash' => $hash,
                        ':user_id' => $user_id
                    ]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, username = :username, email = :email WHERE id = :user_id');
                    $stmt->execute([
                        ':first_name' => $firstName,
                        ':last_name' => $lastName,
                        ':username' => $username,
                        ':email' => $email,
                        ':user_id' => $user_id
                    ]);
                }

                // Update session data
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                $success = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Profile - My Site</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site</span></div>
      <nav>
        <a href="index.html">Home</a>
        <a href="help.html">Help</a>
        <a href="profile.php">Profile</a>
        <a href="my_lists.php">My Lists</a>
        <a href="logout.php">Logout</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>Edit Profile</h1>
    <p class="muted">Update your account information</p>

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
        <strong>Profile updated successfully!</strong>
        <p style="margin:8px 0 0 0;"><a href="profile.php" style="color:var(--accent);">Return to profile</a></p>
      </div>
    <?php else: ?>
    <form class="card" method="post" action="edit_profile.php" style="padding:16px; display:grid; gap:12px;">
      <h3 style="margin-top:0;">Personal Information</h3>
      
      <div>
        <label for="first_name">First name</label><br>
        <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      
      <div>
        <label for="last_name">Last name</label><br>
        <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8');?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      
      <div>
        <label for="username">Username</label><br>
        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8');?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      
      <div>
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8');?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>

      <hr style="border:none; border-top:1px solid var(--border-color); margin:16px 0;">

      <h3>Change Password (Optional)</h3>
      <p class="muted" style="margin-top:0;">Leave password fields empty if you don't want to change your password.</p>
      
      <div>
        <label for="current_password">Current password</label><br>
        <input type="password" id="current_password" name="current_password" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      
      <div>
        <label for="new_password">New password</label><br>
        <input type="password" id="new_password" name="new_password" minlength="6" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>
      
      <div>
        <label for="confirm_password">Confirm new password</label><br>
        <input type="password" id="confirm_password" name="confirm_password" minlength="6" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
      </div>

      <div style="display:flex; gap:12px; margin-top:16px;">
        <button type="submit" class="theme-toggle" style="font-weight:600;">Update Profile</button>
        <a href="profile.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px; text-align:center;">Cancel</a>
      </div>
    </form>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>

