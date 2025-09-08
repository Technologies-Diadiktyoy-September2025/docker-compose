<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];
$search_term = $_GET['search'] ?? '';

echo "<h1>Video Title Search Test</h1>";
echo "<form method='GET'>";
echo "<input type='text' name='search' value='" . htmlspecialchars($search_term) . "' placeholder='Search video titles...'>";
echo "<button type='submit'>Search</button>";
echo "</form>";

if (!empty($search_term)) {
    echo "<h2>Search Results for: " . htmlspecialchars($search_term) . "</h2>";
    
    try {
        // Test the exact same query as in search_results.php
        $query = "
            SELECT DISTINCT
                ul.id,
                ul.list_name as name,
                ul.description,
                ul.is_public,
                ul.created_at,
                ul.updated_at,
                u.id as user_id,
                u.username,
                u.first_name,
                u.last_name,
                u.email,
                COUNT(DISTINCT li.id) as video_count,
                GROUP_CONCAT(DISTINCT sc.title SEPARATOR ' | ') as video_titles
            FROM user_lists ul
            INNER JOIN users u ON ul.user_id = u.id
            LEFT JOIN list_items li ON ul.id = li.list_id
            LEFT JOIN streaming_content sc ON li.content_id = sc.id
            WHERE 1=1
            AND (ul.is_public = 1 OR ul.user_id = :current_user_id)
            AND (ul.list_name LIKE :search_text OR ul.description LIKE :search_text OR sc.title LIKE :search_text OR sc.description LIKE :search_text)
            GROUP BY ul.id, ul.list_name, ul.description, ul.is_public, ul.created_at, ul.updated_at, 
                     u.id, u.username, u.first_name, u.last_name, u.email
            ORDER BY ul.created_at DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':current_user_id' => $current_user_id,
            ':search_text' => "%$search_term%"
        ]);
        
        $results = $stmt->fetchAll();
        
        echo "<p>Found " . count($results) . " results</p>";
        
        foreach ($results as $result) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h3>" . htmlspecialchars($result['name']) . "</h3>";
            echo "<p><strong>By:</strong> " . htmlspecialchars($result['first_name'] . ' ' . $result['last_name']) . " (@" . htmlspecialchars($result['username']) . ")</p>";
            echo "<p><strong>Videos:</strong> " . $result['video_count'] . "</p>";
            if ($result['video_titles']) {
                echo "<p><strong>Video Titles:</strong> " . htmlspecialchars($result['video_titles']) . "</p>";
            }
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr>";
echo "<h2>All Lists with Videos (for reference)</h2>";

try {
    $stmt = $pdo->query("
        SELECT 
            ul.list_name,
            u.username,
            COUNT(li.id) as video_count,
            GROUP_CONCAT(sc.title SEPARATOR ' | ') as video_titles
        FROM user_lists ul
        JOIN users u ON ul.user_id = u.id
        LEFT JOIN list_items li ON ul.id = li.list_id
        LEFT JOIN streaming_content sc ON li.content_id = sc.id
        WHERE (ul.is_public = 1 OR ul.user_id = $current_user_id)
        GROUP BY ul.id, ul.list_name, u.username
        HAVING video_count > 0
        ORDER BY ul.list_name
    ");
    
    $results = $stmt->fetchAll();
    
    foreach ($results as $result) {
        echo "<div style='border: 1px solid #eee; padding: 5px; margin: 5px 0;'>";
        echo "<strong>" . htmlspecialchars($result['list_name']) . "</strong> by " . htmlspecialchars($result['username']) . " (" . $result['video_count'] . " videos)<br>";
        echo "<small>" . htmlspecialchars($result['video_titles']) . "</small>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
