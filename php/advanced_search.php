<?php
session_start();
require_once __DIR__ . '/db.php';

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
  <title>Advanced Search - Streaming Lists</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* SPA-specific styles */
    .spa-container {
      display: none;
    }
    
    .spa-container.active {
      display: block;
    }
    
    .search-form {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 24px;
      margin-bottom: 24px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--text-color);
    }
    
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    
    .form-row-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 16px;
    }
    
    .form-control {
      width: 100%;
      padding: 12px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      background: var(--bg-color);
      color: var(--text-color);
      font-size: 14px;
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    
    .search-options {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 24px;
    }
    
    .search-options h3 {
      margin-top: 0;
      margin-bottom: 16px;
      color: var(--text-color);
    }
    
    .checkbox-group {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 12px;
    }
    
    .checkbox-item {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .checkbox-item input[type="checkbox"] {
      margin: 0;
    }
    
    .results-container {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 24px;
    }
    
    .result-item {
      border-bottom: 1px solid var(--border-color);
      padding: 20px 0;
    }
    
    .result-item:last-child {
      border-bottom: none;
    }
    
    .result-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 12px;
    }
    
    .result-title {
      font-size: 18px;
      font-weight: 600;
      color: var(--text-color);
      margin: 0;
    }
    
    .result-meta {
      color: var(--muted-text);
      font-size: 14px;
      margin: 8px 0;
    }
    
    .result-description {
      color: var(--text-color);
      margin: 12px 0;
      line-height: 1.5;
    }
    
    .result-stats {
      display: flex;
      gap: 16px;
      margin-top: 12px;
    }
    
    .stat-item {
      color: var(--muted-text);
      font-size: 14px;
    }
    
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
      margin-top: 24px;
    }
    
    .pagination button {
      padding: 8px 12px;
      border: 1px solid var(--border-color);
      background: var(--bg-color);
      color: var(--text-color);
      border-radius: 4px;
      cursor: pointer;
    }
    
    .pagination button:hover:not(:disabled) {
      background: var(--hover-bg);
    }
    
    .pagination button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .pagination .current {
      background: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }
    
    .results-summary {
      color: var(--muted-text);
      margin-bottom: 16px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="brand"><span>My Site</span></div>
      <nav>
        <a href="spa.php">Home</a>
        <a href="spa.php#lists">My Lists</a>
        <a href="spa.php#search">Search</a>
        <a href="spa.php#profiles">Profiles</a>
        <a href="spa.php#profile">Profile</a>
        <a href="advanced_search.php" class="active">Advanced Search</a>
        <a href="youtube_connect.php">YouTube</a>
        <a href="logout.php">Logout</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <h1>Advanced Search</h1>
    <p class="muted">Search through all streaming content lists with advanced filters</p>

      <form method="GET" action="advanced_search.php" class="search-form">
        <div class="form-group">
          <label for="search_text">Search Text</label>
          <input type="text" id="search_text" name="search_text" class="form-control" 
                 placeholder="Search in list titles, descriptions, or video titles..." 
                 value="<?php echo htmlspecialchars($_GET['search_text'] ?? '', ENT_QUOTES); ?>">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="date_from">Created From</label>
            <input type="date" id="date_from" name="date_from" class="form-control" 
                   value="<?php echo htmlspecialchars($_GET['date_from'] ?? '', ENT_QUOTES); ?>">
          </div>
          <div class="form-group">
            <label for="date_to">Created To</label>
            <input type="date" id="date_to" name="date_to" class="form-control" 
                   value="<?php echo htmlspecialchars($_GET['date_to'] ?? '', ENT_QUOTES); ?>">
          </div>
        </div>

        <div class="form-row-3">
          <div class="form-group">
            <label for="user_first_name">User First Name</label>
            <input type="text" id="user_first_name" name="user_first_name" class="form-control" 
                   placeholder="First name..." 
                   value="<?php echo htmlspecialchars($_GET['user_first_name'] ?? '', ENT_QUOTES); ?>">
          </div>
          <div class="form-group">
            <label for="user_last_name">User Last Name</label>
            <input type="text" id="user_last_name" name="user_last_name" class="form-control" 
                   placeholder="Last name..." 
                   value="<?php echo htmlspecialchars($_GET['user_last_name'] ?? '', ENT_QUOTES); ?>">
          </div>
          <div class="form-group">
            <label for="user_username">Username</label>
            <input type="text" id="user_username" name="user_username" class="form-control" 
                   placeholder="Username..." 
                   value="<?php echo htmlspecialchars($_GET['user_username'] ?? '', ENT_QUOTES); ?>">
          </div>
        </div>

        <div class="form-group">
          <label for="user_email">User Email</label>
          <input type="email" id="user_email" name="user_email" class="form-control" 
                 placeholder="Email address..." 
                 value="<?php echo htmlspecialchars($_GET['user_email'] ?? '', ENT_QUOTES); ?>">
        </div>

        <div class="search-options">
          <h3>Search Options</h3>
          <div class="checkbox-group">
            <div class="checkbox-item">
              <input type="checkbox" id="search_list_titles" name="search_list_titles" value="1" 
                     <?php echo isset($_GET['search_list_titles']) ? 'checked' : ''; ?>>
              <label for="search_list_titles">Search in list titles</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" id="search_list_descriptions" name="search_list_descriptions" value="1" 
                     <?php echo isset($_GET['search_list_descriptions']) ? 'checked' : ''; ?>>
              <label for="search_list_descriptions">Search in list descriptions</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" id="search_video_titles" name="search_video_titles" value="1" 
                     <?php echo isset($_GET['search_video_titles']) ? 'checked' : ''; ?>>
              <label for="search_video_titles">Search in video titles</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" id="search_video_descriptions" name="search_video_descriptions" value="1" 
                     <?php echo isset($_GET['search_video_descriptions']) ? 'checked' : ''; ?>>
              <label for="search_video_descriptions">Search in video descriptions</label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="per_page">Results per page</label>
            <select id="per_page" name="per_page" class="form-control">
              <option value="10" <?php echo ($_GET['per_page'] ?? '10') == '10' ? 'selected' : ''; ?>>10</option>
              <option value="25" <?php echo ($_GET['per_page'] ?? '10') == '25' ? 'selected' : ''; ?>>25</option>
              <option value="50" <?php echo ($_GET['per_page'] ?? '10') == '50' ? 'selected' : ''; ?>>50</option>
            </select>
          </div>
          <div class="form-group">
            <label for="sort_by">Sort by</label>
            <select id="sort_by" name="sort_by" class="form-control">
              <option value="created_at" <?php echo ($_GET['sort_by'] ?? 'created_at') == 'created_at' ? 'selected' : ''; ?>>Date Created</option>
              <option value="updated_at" <?php echo ($_GET['sort_by'] ?? 'created_at') == 'updated_at' ? 'selected' : ''; ?>>Date Updated</option>
              <option value="name" <?php echo ($_GET['sort_by'] ?? 'created_at') == 'name' ? 'selected' : ''; ?>>List Name</option>
              <option value="username" <?php echo ($_GET['sort_by'] ?? 'created_at') == 'username' ? 'selected' : ''; ?>>Username</option>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
          <button type="submit" class="btn btn-primary">Search</button>
          <a href="advanced_search.php" class="btn btn-secondary">Clear</a>
          <a href="advanced_search.php?show_all=1" class="btn btn-secondary">Show All Lists</a>
        </div>
      </form>

      <?php
      // Process search if any parameters are provided (including checkboxes) or if show_all is requested
      if (!empty($_GET['search_text']) || !empty($_GET['date_from']) || !empty($_GET['date_to']) || 
          !empty($_GET['user_first_name']) || !empty($_GET['user_last_name']) || 
          !empty($_GET['user_username']) || !empty($_GET['user_email']) ||
          isset($_GET['search_list_titles']) || isset($_GET['search_list_descriptions']) ||
          isset($_GET['search_video_titles']) || isset($_GET['search_video_descriptions']) ||
          !empty($_GET['per_page']) || !empty($_GET['sort_by']) || !empty($_GET['page']) ||
          isset($_GET['show_all'])) {
        
        include __DIR__ . '/search_results.php';
      }
      ?>
    </div>
  </main>

  <script>
    // Theme toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
      const themeToggle = document.querySelector('.theme-toggle');
      const html = document.documentElement;
      
      // Load saved theme
      const savedTheme = localStorage.getItem('theme') || 'light';
      html.setAttribute('data-theme', savedTheme);
      updateThemeButton(savedTheme);
      
      // Theme toggle handler
      themeToggle.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeButton(newTheme);
      });
      
      function updateThemeButton(theme) {
        themeToggle.textContent = theme === 'light' ? 'Dark mode' : 'Light mode';
      }
    });
  </script>
</body>
</html>
