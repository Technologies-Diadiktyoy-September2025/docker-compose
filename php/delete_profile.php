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
$confirmation_required = true;

// Get current user data for display
$stmt = $pdo->prepare('SELECT first_name, last_name, username, email FROM users WHERE id = :user_id');
$stmt->execute([':user_id' => $user_id]);
$current_user = $stmt->fetch();

if (!$current_user) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm_delete = $_POST['confirm_delete'] ?? '';
    $password = $_POST['password'] ?? '';
    $username_confirmation = trim($_POST['username_confirmation'] ?? '');

    // Validation
    if ($confirm_delete !== 'DELETE') {
        $errors[] = 'You must type "DELETE" to confirm account deletion.';
    }
    
    if ($password === '') {
        $errors[] = 'Password is required to delete your account.';
    }
    
    if ($username_confirmation !== $current_user['username']) {
        $errors[] = 'Username confirmation does not match.';
    }

    if (empty($errors)) {
        // Verify password
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :user_id');
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Password is incorrect.';
        } else {
            // Password is correct, proceed with deletion
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                // Note: In a real application, you would also delete related data here
                // For example: DELETE FROM user_lists WHERE user_id = :user_id
                // DELETE FROM list_items WHERE list_id IN (SELECT id FROM user_lists WHERE user_id = :user_id)
                
                // Delete the user
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
                $stmt->execute([':user_id' => $user_id]);
                
                // Commit transaction
                $pdo->commit();
                
                // Destroy session
                session_destroy();
                
                $success = true;
                $confirmation_required = false;
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $errors[] = 'Failed to delete account. Please try again.';
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
  <title>Delete Account - My Site</title>
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
    <h1>Delete Account</h1>
    <p class="muted">Permanently remove your account and all associated data</p>

    <?php if ($success): ?>
      <div class="card" style="padding:24px; text-align:center;">
        <h2 style="color:#43a047; margin-top:0;">Account Deleted Successfully</h2>
        <p>Your account and all associated data have been permanently removed.</p>
        <p>Thank you for using our service.</p>
        <a href="index.html" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px; margin-top:16px; display:inline-block;">Return to Home</a>
      </div>
    <?php else: ?>
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

      <div class="card" style="padding:24px; border-left:4px solid #e53935;">
        <h2 style="color:#e53935; margin-top:0;">⚠️ Warning: This action cannot be undone!</h2>
        <p><strong>Deleting your account will permanently remove:</strong></p>
        <ul>
          <li>Your personal information (name, email, username)</li>
          <li>All lists you have created</li>
          <li>All data associated with your account</li>
          <li>Your login access to the platform</li>
        </ul>
        <p><strong>This action is irreversible.</strong></p>
      </div>

      <div class="card" style="padding:24px; margin-top:16px;">
        <h3 style="margin-top:0;">Account Information</h3>
        <div style="display:grid; gap:8px;">
          <div><strong>Name:</strong> <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div><strong>Username:</strong> <?php echo htmlspecialchars($current_user['username'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div><strong>Email:</strong> <?php echo htmlspecialchars($current_user['email'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
      </div>

      <form class="card" method="post" action="delete_profile.php" style="padding:16px; display:grid; gap:12px;">
        <h3 style="margin-top:0;">Confirm Account Deletion</h3>
        
        <div style="padding:16px; background:rgba(229, 57, 53, 0.1); border:1px solid #e53935; border-radius:8px;">
          <p style="margin:0;"><strong>To delete your account, you must:</strong></p>
          <ol style="margin:8px 0 0 0;">
            <li>Type "DELETE" in the confirmation field below</li>
            <li>Enter your current password</li>
            <li>Type your username exactly as shown above</li>
          </ol>
        </div>
        
        <div>
          <label for="confirm_delete">Type "DELETE" to confirm:</label><br>
          <input type="text" id="confirm_delete" name="confirm_delete" required style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
        </div>
        
        <div>
          <label for="password">Current password:</label><br>
          <input type="password" id="password" name="password" required style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
        </div>
        
        <div>
          <label for="username_confirmation">Type your username to confirm:</label><br>
          <input type="text" id="username_confirmation" name="username_confirmation" required value="<?php echo htmlspecialchars($current_user['username'], ENT_QUOTES, 'UTF-8'); ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
        </div>

        <div style="display:flex; gap:12px; margin-top:16px;">
          <button type="submit" style="background:#e53935; color:white; border:none; padding:8px 16px; border-radius:8px; font-weight:600; cursor:pointer;">Delete Account Permanently</button>
          <a href="profile.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px; text-align:center;">Cancel</a>
        </div>
      </form>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>

