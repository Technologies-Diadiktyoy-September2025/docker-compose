<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only allow admin users to export data (you can modify this as needed)
// For now, we'll allow any logged-in user, but you can add admin check here
$user_id = $_SESSION['user_id'];

try {
    // Fetch all lists with their contents
    $query = "
        SELECT 
            ul.id as list_id,
            ul.list_name,
            ul.description,
            ul.created_at as list_created_at,
            ul.is_public,
            u.username,
            u.first_name,
            u.last_name,
            u.email,
            u.created_at as user_created_at,
            sc.id as content_id,
            sc.title as video_title,
            sc.description as video_description,
            sc.youtube_video_id,
            sc.channel_title,
            sc.thumbnail_url,
            sc.published_at,
            li.added_at
        FROM user_lists ul
        JOIN users u ON ul.user_id = u.id
        LEFT JOIN list_items li ON ul.id = li.list_id
        LEFT JOIN streaming_content sc ON li.content_id = sc.id
        ORDER BY ul.id, li.added_at
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize data by lists
    $lists = [];
    $user_hashes = []; // Store user hashes to maintain consistency
    
    foreach ($results as $row) {
        $list_id = $row['list_id'];
        
        // Create unique identifier for user (hash of username + email + created_at)
        $user_key = $row['username'] . $row['email'] . $row['user_created_at'];
        if (!isset($user_hashes[$user_key])) {
            $user_hashes[$user_key] = 'user_' . substr(hash('sha256', $user_key), 0, 12);
        }
        $user_identifier = $user_hashes[$user_key];
        
        // Initialize list if not exists
        if (!isset($lists[$list_id])) {
            $lists[$list_id] = [
                'list_id' => $list_id,
                'list_name' => $row['list_name'],
                'description' => $row['description'] ?: null,
                'created_at' => $row['list_created_at'],
                'is_public' => (bool)$row['is_public'],
                'creator' => [
                    'identifier' => $user_identifier,
                    'account_created' => $row['user_created_at']
                ],
                'items' => []
            ];
        }
        
        // Add content item if exists
        if ($row['content_id']) {
            $lists[$list_id]['items'][] = [
                'added_at' => $row['added_at'],
                'content' => [
                    'content_id' => $row['content_id'],
                    'title' => $row['video_title'],
                    'description' => $row['video_description'] ?: null,
                    'youtube_video_id' => $row['youtube_video_id'],
                    'channel_name' => $row['channel_title'] ?: null,
                    'thumbnail_url' => $row['thumbnail_url'] ?: null,
                    'published_at' => $row['published_at'] ?: null
                ]
            ];
        }
    }
    
    // Convert to array for YAML conversion
    $export_data = [
        'export_info' => [
            'exported_at' => date('Y-m-d H:i:s'),
            'total_lists' => count($lists),
            'total_users' => count($user_hashes),
            'description' => 'Open data export of streaming content lists with privacy protection'
        ],
        'lists' => array_values($lists)
    ];
    
    // Generate YAML content
    $yaml_content = generateYAML($export_data);
    
    // Set headers for download
    $filename = 'streaming_lists_export_' . date('Y-m-d_H-i-s') . '.yaml';
    header('Content-Type: application/x-yaml');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($yaml_content));
    
    // Output YAML content
    echo $yaml_content;
    exit;
    
} catch (Exception $e) {
    error_log("Export error: " . $e->getMessage());
    die("Error generating export: " . $e->getMessage());
}

/**
 * Simple YAML generator function
 */
function generateYAML($data, $indent = 0) {
    $yaml = '';
    $spaces = str_repeat('  ', $indent);
    
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            if (is_numeric($key)) {
                // Array item
                $yaml .= $spaces . "- ";
                if (is_array($value) && !isAssociative($value)) {
                    $yaml .= "\n" . generateYAML($value, $indent + 1);
                } else {
                    $yaml .= "\n" . generateYAML($value, $indent + 1);
                }
            } else {
                // Associative array
                $yaml .= $spaces . $key . ":\n";
                $yaml .= generateYAML($value, $indent + 1);
            }
        } else {
            // Simple value
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $value = 'null';
            } elseif (is_string($value) && (strpos($value, "\n") !== false || strpos($value, '"') !== false || strpos($value, "'") !== false)) {
                $value = '"' . addslashes($value) . '"';
            }
            
            if (is_numeric($key)) {
                $yaml .= $spaces . "- " . $value . "\n";
            } else {
                $yaml .= $spaces . $key . ": " . $value . "\n";
            }
        }
    }
    
    return $yaml;
}

/**
 * Check if array is associative
 */
function isAssociative($array) {
    return array_keys($array) !== range(0, count($array) - 1);
}
?>
