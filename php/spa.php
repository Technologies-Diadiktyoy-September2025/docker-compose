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
$username = $_SESSION['username'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Site - Streaming Content Manager</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* SPA-specific styles */
    .spa-container {
      display: none;
    }
    
    .spa-container.active {
      display: block;
    }
    
    .loading {
      text-align: center;
      padding: 40px;
      color: var(--muted-text);
    }
    
    .error {
      background: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 4px;
      margin: 16px 0;
    }
    
    .success {
      background: #d4edda;
      color: #155724;
      padding: 12px;
      border-radius: 4px;
      margin: 16px 0;
    }
    
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
      background-color: var(--bg-color);
      margin: 5% auto;
      padding: 20px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      width: 90%;
      max-width: 600px;
      max-height: 80vh;
      overflow-y: auto;
    }
    
    .close {
      color: var(--muted-text);
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    
    .close:hover {
      color: var(--text-color);
    }
    
    .video-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 16px;
      margin-top: 16px;
    }
    
    .video-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      overflow: hidden;
      transition: transform 0.2s;
    }
    
    .video-card:hover {
      transform: translateY(-2px);
    }
    
    .video-thumbnail {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }
    
    .video-info {
      padding: 12px;
    }
    
    .video-title {
      font-weight: 600;
      margin: 0 0 8px 0;
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .video-channel {
      color: var(--muted-text);
      font-size: 0.9rem;
      margin: 0 0 8px 0;
    }
    
    .video-actions {
      display: flex;
      gap: 8px;
      margin-top: 12px;
    }
    
    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.9rem;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }
    
    .btn-primary {
      background: var(--accent);
      color: white;
    }
    
    .btn-secondary {
      background: var(--card-bg);
      color: var(--text-color);
      border: 1px solid var(--border-color);
    }
    
    .btn-danger {
      background: #e53935;
      color: white;
    }
    
    .btn:hover {
      opacity: 0.9;
    }
    
    .list-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 16px;
      margin-top: 16px;
    }
    
    .list-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 16px;
      transition: transform 0.2s;
    }
    
    .list-card:hover {
      transform: translateY(-2px);
    }
    
    .list-name {
      font-weight: 600;
      margin: 0 0 8px 0;
      color: var(--accent);
    }
    
    .list-meta {
      color: var(--muted-text);
      font-size: 0.9rem;
      margin: 0 0 12px 0;
    }
    
    .list-actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    
    .search-form {
      display: flex;
      gap: 12px;
      margin-bottom: 20px;
    }
    
    .search-form input {
      flex: 1;
      padding: 10px;
      border: 1px solid var(--border-color);
      background: var(--bg-color);
      color: var(--text-color);
      border-radius: 8px;
    }
    
    .search-form button {
      padding: 10px 20px;
      background: var(--accent);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
    
    .player-container {
      position: relative;
      width: 100%;
      height: 0;
      padding-bottom: 56.25%;
      background: #000;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 20px;
    }
    
    .player-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: none;
    }
    
    .playlist-sidebar {
      max-height: 400px;
      overflow-y: auto;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      background: var(--card-bg);
    }
    
    .playlist-item {
      display: flex;
      gap: 12px;
      padding: 12px;
      border-bottom: 1px solid var(--border-color);
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .playlist-item:last-child {
      border-bottom: none;
    }
    
    .playlist-item:hover {
      background: rgba(127,127,127,0.1);
    }
    
    .playlist-item.current {
      background: var(--accent);
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
        <a href="#" data-route="home">Home</a>
        <a href="#" data-route="lists">My Lists</a>
        <a href="#" data-route="search">Search</a>
        <a href="#" data-route="profiles">Profiles</a>
        <a href="#" data-route="profile">Profile</a>
        <a href="advanced_search.php">Advanced Search</a>
        <a href="export_data.php">Export Data</a>
        <a href="youtube_connect.php">YouTube</a>
        <a href="logout.php">Logout</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <!-- Loading indicator -->
    <div id="loading" class="loading" style="display: none;">
      <p>Loading...</p>
    </div>

    <!-- Error/Success messages -->
    <div id="messages"></div>

    <!-- Home Page -->
    <div id="home" class="spa-container active">
      <h1>Welcome to Streaming Content Manager</h1>
      <p class="muted">Manage your YouTube playlists and discover new content</p>
      
      <div class="card" style="padding: 24px;">
        <h2>Quick Actions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">
          <a href="#" data-route="lists" class="btn btn-primary" style="text-decoration: none; padding: 16px; text-align: center;">
            <h3 style="margin: 0 0 8px 0;">📋 My Lists</h3>
            <p style="margin: 0; font-size: 0.9rem;">View and manage your playlists</p>
          </a>
          <a href="#" data-route="search" class="btn btn-primary" style="text-decoration: none; padding: 16px; text-align: center;">
            <h3 style="margin: 0 0 8px 0;">🔍 Search Videos</h3>
            <p style="margin: 0; font-size: 0.9rem;">Find and add YouTube content</p>
          </a>
          <a href="#" data-route="profile" class="btn btn-primary" style="text-decoration: none; padding: 16px; text-align: center;">
            <h3 style="margin: 0 0 8px 0;">👤 Profile</h3>
            <p style="margin: 0; font-size: 0.9rem;">Manage your account</p>
          </a>
        </div>
      </div>
    </div>

    <!-- Lists Page -->
    <div id="lists" class="spa-container">
      <h1>My Lists</h1>
      <p class="muted">Manage your streaming content lists</p>
      
      <div class="card" style="padding: 24px;">
        <h2>Create New List</h2>
        <form id="create-list-form" style="display: grid; gap: 12px;">
          <div>
            <label for="list-name">List Name *</label><br>
            <input type="text" id="list-name" name="list_name" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color); border-radius: 8px;">
          </div>
          <div>
            <label for="list-description">Description</label><br>
            <textarea id="list-description" name="description" rows="3" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color); border-radius: 8px; resize: vertical;"></textarea>
          </div>
          <div>
            <label style="display: flex; align-items: center; gap: 8px;">
              <input type="checkbox" id="list-public" name="is_public" value="1">
              <span>Make this list public (visible to other users)</span>
            </label>
          </div>
          <div>
            <button type="submit" class="btn btn-primary">Create List</button>
          </div>
        </form>
      </div>

      <div class="card" style="padding: 24px; margin-top: 16px;">
        <h2>My Lists</h2>
        <div id="user-lists" class="list-grid">
          <!-- Lists will be loaded here -->
        </div>
      </div>

      <div class="card" style="padding: 24px; margin-top: 16px;">
        <h2>Lists from Users You Follow</h2>
        <div id="followed-lists" class="list-grid">
          <!-- Followed lists will be loaded here -->
        </div>
      </div>
    </div>

    <!-- Search Page -->
    <div id="search" class="spa-container">
      <h1>Search YouTube Content</h1>
      <p class="muted">Search for videos and add them to your lists</p>
      
      <div class="card" style="padding: 24px;">
        <form id="search-form" class="search-form">
          <input type="text" id="search-query" placeholder="Enter keywords to search for videos..." required>
          <button type="submit">Search Videos</button>
        </form>
        
        <div id="search-results" class="video-grid">
          <!-- Search results will be loaded here -->
        </div>
      </div>
    </div>

    <!-- Profiles Page -->
    <div id="profiles" class="spa-container">
      <h1>User Profiles</h1>
      <p class="muted">Search for users and follow them to see their public lists</p>
      
      <div class="card" style="padding: 24px;">
        <h2>Search Users</h2>
        <form id="search-profiles-form" class="search-form" onsubmit="return false;">
          <input type="text" id="profile-search-query" placeholder="Enter username, first name, or last name..." required>
          <button type="button" id="search-profiles-btn">Search Users</button>
        </form>
        
        <div id="profile-search-results" class="list-grid">
          <!-- Profile search results will be loaded here -->
        </div>
      </div>

      <div class="card" style="padding: 24px; margin-top: 16px;">
        <h2>Users You Follow</h2>
        <div id="followed-users" class="list-grid">
          <!-- Followed users will be loaded here -->
        </div>
      </div>
    </div>

    <!-- Profile Page -->
    <div id="profile" class="spa-container">
      <h1>My Profile</h1>
      <p class="muted">Manage your account information</p>
      
      <div class="card" style="padding: 24px;">
        <h2>Account Information</h2>
        <div style="display: grid; gap: 16px;">
          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; align-items: center;">
            <strong>Name:</strong>
            <span><?php echo htmlspecialchars($first_name . ' ' . $last_name, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; align-items: center;">
            <strong>Username:</strong>
            <span><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; align-items: center;">
            <strong>Email:</strong>
            <span><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>
        
        <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
          <a href="delete_profile.php" class="btn btn-danger">Delete Account</a>
        </div>
      </div>
    </div>

    <!-- List View Page -->
    <div id="list-view" class="spa-container">
      <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 24px;">
        <div>
          <h1 id="list-view-title">List Details</h1>
          <p id="list-view-meta" class="muted"></p>
        </div>
        <div style="display: flex; gap: 8px;">
          <button id="edit-list-btn" class="btn btn-secondary" style="display: none;">Edit List</button>
          <button id="add-content-btn" class="btn btn-secondary" style="display: none;">Add Content</button>
        </div>
      </div>

      <div id="list-description" class="card" style="padding: 16px; margin-bottom: 16px; display: none;">
        <p id="list-description-text"></p>
      </div>

      <div class="card" style="padding: 24px;">
        <h2 id="list-videos-title">Videos</h2>
        <div id="list-videos">
          <!-- List videos will be loaded here -->
        </div>
      </div>
    </div>

    <!-- User Profile View Page -->
    <div id="user-profile" class="spa-container">
      <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 24px;">
        <div>
          <h1 id="user-profile-name">User Profile</h1>
          <p id="user-profile-meta" class="muted"></p>
        </div>
        <div>
          <button id="follow-user-btn" class="btn btn-primary" style="display: none;">Follow</button>
          <button id="unfollow-user-btn" class="btn btn-secondary" style="display: none;">Unfollow</button>
        </div>
      </div>

      <div class="card" style="padding: 24px;">
        <h2 id="user-lists-title">Public Lists</h2>
        <div id="user-public-lists" class="list-grid">
          <!-- User's public lists will be loaded here -->
        </div>
      </div>
    </div>

    <!-- Video Player Page -->
    <div id="video-player" class="spa-container">
      <div style="display: flex; gap: 20px;">
        <div style="flex: 2;">
          <div id="video-player-container" class="player-container">
            <!-- YouTube player will be loaded here -->
          </div>
          
          <div class="card" style="padding: 20px;">
            <h1 id="video-title">Video Title</h1>
            <p id="video-meta" class="muted"></p>
            <div id="video-description" style="margin-top: 16px;">
              <!-- Video description will be loaded here -->
            </div>
          </div>
        </div>
        
        <div style="flex: 1;">
          <div class="card" style="padding: 20px;">
            <h3 id="playlist-title">Playlist</h3>
            <div id="playlist-sidebar" class="playlist-sidebar">
              <!-- Playlist will be loaded here -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modals -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <div id="modal-body">
        <!-- Modal content will be loaded here -->
      </div>
    </div>
  </div>

  <p class="footer">© <?php echo date('Y'); ?> My Site</p>

  <script src="script.js"></script>
  <script src="spa.js"></script>
</body>
</html>
