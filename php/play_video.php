<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$content_id = $_GET['id'] ?? null;
$list_id = $_GET['list_id'] ?? null;
$video = null;
$list = null;
$playlist_videos = [];
$current_index = -1;
$error = null;

if (!$content_id || !is_numeric($content_id)) {
    $error = 'Invalid video ID.';
} else {
    // Get video information
    $stmt = $pdo->prepare('SELECT * FROM streaming_content WHERE id = :content_id');
    $stmt->execute([':content_id' => $content_id]);
    $video = $stmt->fetch();
    
    if (!$video) {
        $error = 'Video not found.';
    } else {
        // If list_id is provided, get playlist information
        if ($list_id && is_numeric($list_id)) {
            // Get list information
            $stmt = $pdo->prepare('
                SELECT ul.*, u.username
                FROM user_lists ul
                LEFT JOIN users u ON ul.user_id = u.id
                WHERE ul.id = :list_id
            ');
            $stmt->execute([':list_id' => $list_id]);
            $list = $stmt->fetch();
            
            if ($list) {
                // Check if user can view this list
                if ($list['user_id'] == $user_id || $list['is_public']) {
                    // Get all videos in the list for playlist
                    $stmt = $pdo->prepare('
                        SELECT sc.*, li.position_order
                        FROM list_items li
                        INNER JOIN streaming_content sc ON li.content_id = sc.id
                        WHERE li.list_id = :list_id
                        ORDER BY li.position_order ASC, li.added_at ASC
                    ');
                    $stmt->execute([':list_id' => $list_id]);
                    $playlist_videos = $stmt->fetchAll();
                    
                    // Find current video index in playlist
                    foreach ($playlist_videos as $index => $playlist_video) {
                        if ($playlist_video['id'] == $content_id) {
                            $current_index = $index;
                            break;
                        }
                    }
                }
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
  <title><?php echo $video ? htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') : 'Video Not Found'; ?> - My Site</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .video-container {
      position: relative;
      width: 100%;
      height: 0;
      padding-bottom: 56.25%; /* 16:9 aspect ratio */
      background: #000;
      border-radius: 8px;
      overflow: hidden;
    }
    
    .video-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: none;
    }
    
    .playlist {
      max-height: 400px;
      overflow-y: auto;
    }
    
    .playlist-item {
      display: flex;
      gap: 12px;
      padding: 8px;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .playlist-item:hover {
      background: var(--card-bg);
    }
    
    .playlist-item.current {
      background: var(--accent);
      color: white;
    }
    
    .playlist-item.current a {
      color: white;
    }
    
    .playlist-thumbnail {
      width: 80px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
      flex-shrink: 0;
    }
    
    .playlist-info {
      flex: 1;
      min-width: 0;
    }
    
    .playlist-title {
      font-weight: 600;
      margin: 0 0 4px 0;
      font-size: 0.9rem;
      line-height: 1.3;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
    
    .playlist-channel {
      font-size: 0.8rem;
      color: var(--muted-text);
      margin: 0;
    }
    
    .playlist-item.current .playlist-channel {
      color: rgba(255, 255, 255, 0.8);
    }
  </style>
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
    <?php if ($error): ?>
      <div class="card" style="padding:24px; text-align:center;">
        <h1 style="color:#e53935;">❌ Error</h1>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <a href="my_lists.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Back to My Lists</a>
      </div>
    <?php else: ?>
      <!-- Video Player -->
      <div class="card" style="padding:24px;">
        <h1 style="margin-top:0; margin-bottom:16px;"><?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        
        <div class="video-container">
          <iframe 
            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video['youtube_video_id'], ENT_QUOTES, 'UTF-8'); ?>?autoplay=1&rel=0" 
            allowfullscreen
            allow="autoplay; encrypted-media">
          </iframe>
        </div>
        
        <div style="margin-top:16px;">
          <p style="margin:0 0 8px 0; color:var(--muted-text);">
            by <?php echo htmlspecialchars($video['channel_title'], ENT_QUOTES, 'UTF-8'); ?>
            <?php if ($video['published_at']): ?>
              • Published <?php echo date('M j, Y', strtotime($video['published_at'])); ?>
            <?php endif; ?>
          </p>
          
          <?php if ($video['description']): ?>
            <div style="margin-top:16px;">
              <h3>Description</h3>
              <p style="white-space: pre-wrap; line-height: 1.6;"><?php echo htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Playlist (if viewing from a list) -->
      <?php if (!empty($playlist_videos) && $list): ?>
        <div class="card" style="padding:24px; margin-top:16px;">
          <h2 style="margin-top:0;">
            Playlist: <?php echo htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?>
            <span style="font-size:0.8rem; font-weight:normal; color:var(--muted-text);">
              (<?php echo $current_index + 1; ?> of <?php echo count($playlist_videos); ?>)
            </span>
          </h2>
          
          <div class="playlist">
            <?php foreach ($playlist_videos as $index => $playlist_video): ?>
              <div class="playlist-item <?php echo $index === $current_index ? 'current' : ''; ?>">
                <img src="<?php echo htmlspecialchars($playlist_video['thumbnail_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                     alt="Video thumbnail" class="playlist-thumbnail">
                
                <div class="playlist-info">
                  <h4 class="playlist-title">
                    <a href="play_video.php?id=<?php echo $playlist_video['id']; ?>&list_id=<?php echo $list['id']; ?>" 
                       style="color:inherit; text-decoration:none;">
                      <?php echo htmlspecialchars($playlist_video['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  </h4>
                  <p class="playlist-channel"><?php echo htmlspecialchars($playlist_video['channel_title'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <div style="margin-top:16px; display:flex; gap:8px;">
            <?php if ($current_index > 0): ?>
              <a href="play_video.php?id=<?php echo $playlist_videos[$current_index - 1]['id']; ?>&list_id=<?php echo $list['id']; ?>" 
                 class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">← Previous</a>
            <?php endif; ?>
            
            <?php if ($current_index < count($playlist_videos) - 1): ?>
              <a href="play_video.php?id=<?php echo $playlist_videos[$current_index + 1]['id']; ?>&list_id=<?php echo $list['id']; ?>" 
                 class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Next →</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Navigation -->
      <div style="margin-top:16px; display:flex; gap:8px;">
        <?php if ($list): ?>
          <a href="view_list.php?id=<?php echo $list['id']; ?>" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">Back to List</a>
        <?php endif; ?>
        <a href="my_lists.php" class="theme-toggle" style="text-decoration:none; font-weight:600; padding:8px 16px;">All Lists</a>
      </div>
    <?php endif; ?>

    <p class="footer">© <?php echo date('Y'); ?> My Site</p>
  </main>

  <script src="script.js"></script>
</body>
</html>
