<?php
/**
 * Setup script for streaming content management system
 * Run this script to create the necessary database tables
 */

require_once __DIR__ . '/db.php';

echo "<h1>Streaming Content System Setup</h1>";

try {
    // Read and execute the streaming schema
    $sql = file_get_contents(__DIR__ . '/streaming_schema.sql');
    
    if ($sql === false) {
        throw new Exception('Could not read streaming_schema.sql file');
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty statements and comments
        }
        
        try {
            $pdo->exec($statement);
            $success_count++;
            echo "<p style='color: green;'>✅ Executed: " . substr($statement, 0, 50) . "...</p>";
        } catch (PDOException $e) {
            $error_count++;
            echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<h2>Setup Complete</h2>";
    echo "<p><strong>Successful operations:</strong> $success_count</p>";
    echo "<p><strong>Errors:</strong> $error_count</p>";
    
    if ($error_count === 0) {
        echo "<div style='padding: 16px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 16px 0;'>";
        echo "<h3 style='color: #155724; margin-top: 0;'>✅ Setup Successful!</h3>";
        echo "<p style='color: #155724; margin-bottom: 0;'>All database tables have been created successfully.</p>";
        echo "</div>";
        
        echo "<h3>Next Steps:</h3>";
        echo "<ol>";
        echo "<li>Configure YouTube API credentials in <code>youtube_config.php</code></li>";
        echo "<li>Set up Google Cloud Console project and enable YouTube Data API v3</li>";
        echo "<li>Create OAuth 2.0 credentials and update the configuration</li>";
        echo "<li>Test the system by creating a list and searching for videos</li>";
        echo "</ol>";
        
        echo "<p><a href='my_lists.php' style='background: var(--accent); color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Go to My Lists</a></p>";
    } else {
        echo "<div style='padding: 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 16px 0;'>";
        echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Setup Failed</h3>";
        echo "<p style='color: #721c24; margin-bottom: 0;'>Some database operations failed. Please check the errors above.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='padding: 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 16px 0;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Setup Error</h3>";
    echo "<p style='color: #721c24; margin-bottom: 0;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<h3>Database Tables Created:</h3>";
echo "<ul>";
echo "<li><strong>user_lists</strong> - User-created streaming content lists</li>";
echo "<li><strong>streaming_content</strong> - YouTube video information</li>";
echo "<li><strong>list_items</strong> - Junction table linking lists to content</li>";
echo "<li><strong>user_follows</strong> - User following relationships</li>";
echo "<li><strong>youtube_credentials</strong> - YouTube API OAuth tokens</li>";
echo "</ul>";

echo "<h3>Features Available:</h3>";
echo "<ul>";
echo "<li>Create and manage streaming content lists</li>";
echo "<li>Search YouTube videos using the YouTube Data API</li>";
echo "<li>Add videos to lists with full metadata</li>";
echo "<li>Play videos with playlist functionality</li>";
echo "<li>Follow other users and view their public lists</li>";
echo "<li>Public/private list visibility controls</li>";
echo "</ul>";
?>
