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
  <title>Export Data - Streaming Lists</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .export-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .export-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 24px;
      margin-bottom: 24px;
    }
    
    .export-info {
      background: var(--bg-color);
      border: 1px solid var(--border-color);
      border-radius: 6px;
      padding: 16px;
      margin: 16px 0;
    }
    
    .export-info h4 {
      margin-top: 0;
      color: var(--text-color);
    }
    
    .export-info ul {
      margin: 8px 0;
      padding-left: 20px;
    }
    
    .export-info li {
      margin: 4px 0;
      color: var(--text-color);
    }
    
    .warning-box {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 6px;
      padding: 16px;
      margin: 16px 0;
    }
    
    .warning-box h4 {
      margin-top: 0;
      color: #000000;
    }
    
    .warning-box p {
      margin: 8px 0;
      color: #000000;
    }
    
    .warning-box ul {
      margin: 8px 0;
      padding-left: 20px;
    }
    
    .warning-box li {
      margin: 4px 0;
      color: #000000;
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
        <a href="advanced_search.php">Advanced Search</a>
        <a href="youtube_connect.php">YouTube</a>
        <a href="export_data.php" class="active">Export Data</a>
        <a href="logout.php">Logout</a>
      </nav>
      <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="Toggle theme">Dark mode</button>
    </div>
  </header>

  <main class="container">
    <div class="export-container">
      <h1>Export Data</h1>
      <p class="muted">Export all streaming content lists as open data in YAML format</p>
      
      <div class="export-card">
        <h2>YAML Export</h2>
        <p>Download all lists and their contents in YAML format for open data purposes.</p>
        
        <div class="export-info">
          <h4>What's included:</h4>
          <ul>
            <li>All public and private lists</li>
            <li>List names, descriptions, and creation dates</li>
            <li>All video content with titles, descriptions, and metadata</li>
            <li>YouTube video IDs and channel information</li>
            <li>Content positioning and addition dates</li>
          </ul>
        </div>
        
        <div class="warning-box">
          <h4>Privacy Protection</h4>
          <p><strong>All personal information is protected:</strong></p>
          <ul>
            <li>User names, emails, and personal details are replaced with unique identifiers</li>
            <li>Identifiers are generated using secure hashing</li>
            <li>Only account creation dates are preserved for research purposes</li>
            <li>No passwords or sensitive data are included</li>
          </ul>
        </div>
        
        <div class="export-info">
          <h4>Data Format:</h4>
          <ul>
            <li><strong>Format:</strong> YAML (YAML Ain't Markup Language)</li>
            <li><strong>Encoding:</strong> UTF-8</li>
            <li><strong>Structure:</strong> Hierarchical with export metadata</li>
            <li><strong>File size:</strong> Varies based on content amount</li>
          </ul>
        </div>
        
        <div style="text-align: center; margin-top: 24px;">
          <a href="export_yaml.php" class="btn btn-primary" style="text-decoration: none; padding: 12px 24px; font-size: 16px;">
            📥 Download YAML Export
          </a>
        </div>
      </div>
      
      <div class="export-card">
        <h3>About Open Data</h3>
        <p>This export is designed for open data purposes, allowing researchers and developers to analyze streaming content patterns while protecting user privacy. The data can be used for:</p>
        <ul>
          <li>Content recommendation research</li>
          <li>Trend analysis in streaming preferences</li>
          <li>Educational and academic studies</li>
          <li>Open source project development</li>
        </ul>
      </div>
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
