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
$search_results = [];
$search_query = '';
$error = null;
$selected_list_id = $_GET['list_id'] ?? null;

// Check YouTube credentials
if (!hasValidYouTubeCredentials($user_id, $pdo)) {
    header('Location: my_lists.php');
    exit;
}

// Get user's lists for dropdown
$stmt = $pdo->prepare('SELECT id, list_name FROM user_lists WHERE user_id = :user_id ORDER BY list_name');
$stmt->execute([':user_id' => $user_id]);
$user_lists = $stmt->fetchAll();

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search') {
    $search_query = trim($_POST['search_query'] ?? '');
    
    if ($search_query === '') {
        $error = 'Please enter a search query.';
    } else {
        // Get user's access token
        $stmt = $pdo->prepare('SELECT access_token FROM youtube_credentials WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $user_id]);
        $credentials = $stmt->fetch();
        
        if ($credentials) {
            $search_results = searchYouTubeVideos($search_query, $credentials['access_token'], 20);
            if (!$search_results) {
                $error = 'Failed to search YouTube. Please try again.';
            }
        } else {
            $error = 'YouTube credentials not found. Please reconnect your account.';
        }
    }
}

// Handle adding content to list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_list') {
    $video_id = $_POST['video_id'] ?? '';
    $list_id = $_POST['list_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $thumbnail_url = $_POST['thumbnail_url'] ?? '';
    $channel_title = $_POST['channel_title'] ?? '';
    $published_at = $_POST['published_at'] ?? '';
    
    if ($video_id && $list_id && $title) {
        try {
            $pdo->beginTransaction();
            
            // Check if content already exists
            $stmt = $pdo->prepare('SELECT id FROM streaming_content WHERE youtube_video_id = :video_id');
            $stmt->execute([':video_id' => $video_id]);
            $existing_content = $stmt->fetch();
            
            if ($existing_content) {
                $content_id = $existing_content['id'];
            } else {
                // Insert new content
                $stmt = $pdo->prepare('
                    INSERT INTO streaming_content (youtube_video_id, title, description, thumbnail_url, channel_title, published_at, added_by_user_id) 
                    VALUES (:video_id, :title, :description, :thumbnail_url, :channel_title, :published_at, :user_id)
                ');
                $stmt->execute([
                    ':video_id' => $video_id,
                    ':title' => $title,
                    ':description' => $description,
                    ':thumbnail_url' => $thumbnail_url,
                    ':channel_title' => $channel_title,
                    ':published_at' => $published_at ? date('Y-m-d H:i:s', strtotime($published_at)) : null,
                    ':user_id' => $user_id
                ]);
                $content_id = $pdo->lastInsertId();
            }
            
            // Check if already in list
            $stmt = $pdo->prepare('SELECT id FROM list_items WHERE list_id = :list_id AND content_id = :content_id');
            $stmt->execute([':list_id' => $list_id, ':content_id' => $content_id]);
            
            if (!$stmt->fetch()) {
                // Add to list
                $stmt = $pdo->prepare('
                    INSERT INTO list_items (list_id, content_id, position_order) 
                    VALUES (:list_id, :content_id, (SELECT COALESCE(MAX(position_order), 0) + 1 FROM list_items li WHERE li.list_id = :list_id))
                ');
                $stmt->execute([':list_id' => $list_id, ':content_id' => $content_id]);
            }
            
            $pdo->commit();
            $error = null; // Clear any previous errors
            $success_message = 'Video added to list successfully!';
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Failed to add video to list.';
        }
    } else {
        $error = 'Missing required information.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Search Content - My Site</title>
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
    <h1>Search YouTube Content</h1>
    <p class="muted">Search for YouTube videos and add them to your lists</p>

    <?php if (isset($success_message)): ?>
      <div class="card" style="padding:12px; border-left:4px solid #43a047;">
        <strong><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <br>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="card" style="padding:12px; border-left:4px solid #e53935;">
        <strong><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <br>
    <?php endif; ?>

    <!-- Search Form -->
    <div class="card" style="padding:24px;">
      <h2 style="margin-top:0;">Search YouTube</h2>
      <form method="post" action="search_content.php" style="display:grid; gap:12px;">
        <input type="hidden" name="action" value="search">
        
        <div>
          <label for="search_query">Search Query</label><br>
          <input type="text" id="search_query" name="search_query" required 
                 value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>"
                 placeholder="Enter keywords to search for videos..."
                 style="width:100%; padding:10px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:8px;">
        </div>
        
        <div>
          <button type="submit" class="theme-toggle" style="font-weight:600;">Search Videos</button>
        </div>
      </form>
    </div>

    <!-- Search Results -->
    <?php if (!empty($search_results) && isset($search_results['items'])): ?>
      <div class="card" style="padding:24px; margin-top:16px;">
        <h2 style="margin-top:0;">Search Results (<?php echo count($search_results['items']); ?> videos)</h2>
        
        <div style="display:grid; gap:16px;">
          <?php foreach ($search_results['items'] as $video): ?>
            <div style="display:flex; gap:16px; padding:16px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:8px;">
              <div style="flex-shrink:0;">
                <img src="<?php echo htmlspecialchars($video['snippet']['thumbnails']['medium']['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                     alt="Video thumbnail" style="width:120px; height:90px; object-fit:cover; border-radius:4px;">
              </div>
              
              <div style="flex:1;">
                <h3 style="margin:0 0 8px 0; font-size:1.1rem;">
                  <?php echo htmlspecialchars($video['snippet']['title'], ENT_QUOTES, 'UTF-8'); ?>
                </h3>
                
                <p style="margin:0 0 8px 0; color:var(--muted-text); font-size:0.9rem;">
                  by <?php echo htmlspecialchars($video['snippet']['channelTitle'], ENT_QUOTES, 'UTF-8'); ?> • 
                  Published <?php echo date('M j, Y', strtotime($video['snippet']['publishedAt'])); ?>
                </p>
                
                <p style="margin:0 0 12px 0; color:var(--muted-text); font-size:0.9rem; line-height:1.4;">
                  <?php echo htmlspecialchars(substr($video['snippet']['description'], 0, 150) . '...', ENT_QUOTES, 'UTF-8'); ?>
                </p>
                
                <!-- Add to List Form -->
                <form method="post" action="search_content.php" style="display:flex; gap:8px; align-items:center;">
                  <input type="hidden" name="action" value="add_to_list">
                  <input type="hidden" name="video_id" value="<?php echo htmlspecialchars($video['id']['videoId'], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="title" value="<?php echo htmlspecialchars($video['snippet']['title'], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="description" value="<?php echo htmlspecialchars($video['snippet']['description'], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="thumbnail_url" value="<?php echo htmlspecialchars($video['snippet']['thumbnails']['medium']['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="channel_title" value="<?php echo htmlspecialchars($video['snippet']['channelTitle'], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="published_at" value="<?php echo htmlspecialchars($video['snippet']['publishedAt'], ENT_QUOTES, 'UTF-8'); ?>">
                  
                  <select name="list_id" required style="padding:6px 8px; border:1px solid var(--border-color); background:var(--bg-color); color:var(--text-color); border-radius:4px;">
                    <option value="">Select a list...</option>
                    <?php foreach ($user_lists as $list): ?>
                      <option value="<?php echo $list['id']; ?>" <?php echo $selected_list_id == $list['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  
                  <button type="submit" class="theme-toggle" style="font-weight:600; padding:6px 12px; font-size:0.9rem;">Add to List</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php elseif ($search_query && empty($search_results)): ?>
      <div class="card" style="padding:24px; margin-top:16px; text-align:center;">
        <h3>No videos found</h3>
        <p>Try different search terms or check your YouTube connection.</p>
      </div>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
