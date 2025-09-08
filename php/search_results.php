<?php
// Session already started in parent file
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Get search parameters
$search_text = $_GET['search_text'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$user_first_name = $_GET['user_first_name'] ?? '';
$user_last_name = $_GET['user_last_name'] ?? '';
$user_username = $_GET['user_username'] ?? '';
$user_email = $_GET['user_email'] ?? '';
$search_list_titles = isset($_GET['search_list_titles']);
$search_list_descriptions = isset($_GET['search_list_descriptions']);
$search_video_titles = isset($_GET['search_video_titles']);
$search_video_descriptions = isset($_GET['search_video_descriptions']);
$per_page = (int)($_GET['per_page'] ?? 10);
$page = (int)($_GET['page'] ?? 1);
$sort_by = $_GET['sort_by'] ?? 'created_at';

// Validate per_page
if (!in_array($per_page, [10, 25, 50])) {
    $per_page = 10;
}

// Validate sort_by
$allowed_sorts = ['created_at', 'updated_at', 'name', 'username'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'created_at';
}

// Calculate offset
$offset = ($page - 1) * $per_page;

try {
    // Build the base query
    $base_query = "
        FROM user_lists ul
        INNER JOIN users u ON ul.user_id = u.id
        LEFT JOIN list_items li ON ul.id = li.list_id
        LEFT JOIN streaming_content sc ON li.content_id = sc.id
        WHERE 1=1
    ";
    
    // Debug: Log the search parameters
    error_log("Search Debug - search_text: '$search_text', search_video_titles: " . ($search_video_titles ? 'true' : 'false'));
    
    $params = [];
    $param_count = 0;
    
    // Add text search conditions
    if (!empty($search_text)) {
        $text_conditions = [];
        
        if ($search_list_titles) {
            $text_conditions[] = "ul.list_name LIKE :search_text_" . ++$param_count;
            $params[":search_text_$param_count"] = "%$search_text%";
        }
        
        if ($search_list_descriptions) {
            $text_conditions[] = "ul.description LIKE :search_text_" . ++$param_count;
            $params[":search_text_$param_count"] = "%$search_text%";
        }
        
        if ($search_video_titles) {
            $text_conditions[] = "sc.title LIKE :search_text_" . ++$param_count;
            $params[":search_text_$param_count"] = "%$search_text%";
        }
        
        if ($search_video_descriptions) {
            $text_conditions[] = "sc.description LIKE :search_text_" . ++$param_count;
            $params[":search_text_$param_count"] = "%$search_text%";
        }
        
        // If no specific search options are selected, search in all fields
        if (empty($text_conditions)) {
            $text_conditions = [
                "ul.list_name LIKE :search_text_1",
                "ul.description LIKE :search_text_2", 
                "sc.title LIKE :search_text_3",
                "sc.description LIKE :search_text_4"
            ];
            $params[":search_text_1"] = "%$search_text%";
            $params[":search_text_2"] = "%$search_text%";
            $params[":search_text_3"] = "%$search_text%";
            $params[":search_text_4"] = "%$search_text%";
        }
        
        $base_query .= " AND (" . implode(" OR ", $text_conditions) . ")";
    }
    
    // Add date range conditions
    if (!empty($date_from)) {
        $base_query .= " AND ul.created_at >= :date_from";
        $params[":date_from"] = $date_from . " 00:00:00";
    }
    
    if (!empty($date_to)) {
        $base_query .= " AND ul.created_at <= :date_to";
        $params[":date_to"] = $date_to . " 23:59:59";
    }
    
    // Add user search conditions
    if (!empty($user_first_name)) {
        $base_query .= " AND u.first_name LIKE :user_first_name";
        $params[":user_first_name"] = "%$user_first_name%";
    }
    
    if (!empty($user_last_name)) {
        $base_query .= " AND u.last_name LIKE :user_last_name";
        $params[":user_last_name"] = "%$user_last_name%";
    }
    
    if (!empty($user_username)) {
        $base_query .= " AND u.username LIKE :user_username";
        $params[":user_username"] = "%$user_username%";
    }
    
    if (!empty($user_email)) {
        $base_query .= " AND u.email LIKE :user_email";
        $params[":user_email"] = "%$user_email%";
    }
    
    // Add visibility condition (only show public lists or user's own lists)
    $base_query .= " AND (ul.is_public = 1 OR ul.user_id = :current_user_id)";
    $params[":current_user_id"] = $current_user_id;
    
    // Build the main query with aggregation
    $main_query = "
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
            COUNT(DISTINCT li.id) as video_count
        $base_query
        GROUP BY ul.id, ul.list_name, ul.description, ul.is_public, ul.created_at, ul.updated_at, 
                 u.id, u.username, u.first_name, u.last_name, u.email
    ";
    
    // Add sorting
    switch ($sort_by) {
        case 'updated_at':
            $main_query .= " ORDER BY ul.updated_at DESC";
            break;
        case 'name':
            $main_query .= " ORDER BY ul.list_name ASC";
            break;
        case 'username':
            $main_query .= " ORDER BY u.username ASC";
            break;
        default:
            $main_query .= " ORDER BY ul.created_at DESC";
            break;
    }
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(DISTINCT ul.id) as total $base_query";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_results = $count_stmt->fetch()['total'];
    
    // Add pagination
    $main_query .= " LIMIT :per_page OFFSET :offset";
    $params[":per_page"] = $per_page;
    $params[":offset"] = $offset;
    
    // Debug: Log the final query
    error_log("Final Query: " . $main_query);
    error_log("Parameters: " . print_r($params, true));
    
    // Execute main query
    $stmt = $pdo->prepare($main_query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // Debug: Log results count
    error_log("Results found: " . count($results));
    
    // Calculate pagination info
    $total_pages = ceil($total_results / $per_page);
    $has_prev = $page > 1;
    $has_next = $page < $total_pages;
    
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
    $results = [];
    $total_results = 0;
    $total_pages = 0;
    $has_prev = false;
    $has_next = false;
}

// Helper function to format date
function formatDate($dateString) {
    return date('M j, Y', strtotime($dateString));
}

// Helper function to build pagination URL
function buildPaginationUrl($page) {
    global $search_text, $date_from, $date_to, $user_first_name, $user_last_name, 
           $user_username, $user_email, $search_list_titles, $search_list_descriptions, 
           $search_video_titles, $search_video_descriptions, $per_page, $sort_by;
    
    $params = [
        'page' => $page,
        'per_page' => $per_page,
        'sort_by' => $sort_by
    ];
    
    if (!empty($search_text)) $params['search_text'] = $search_text;
    if (!empty($date_from)) $params['date_from'] = $date_from;
    if (!empty($date_to)) $params['date_to'] = $date_to;
    if (!empty($user_first_name)) $params['user_first_name'] = $user_first_name;
    if (!empty($user_last_name)) $params['user_last_name'] = $user_last_name;
    if (!empty($user_username)) $params['user_username'] = $user_username;
    if (!empty($user_email)) $params['user_email'] = $user_email;
    if ($search_list_titles) $params['search_list_titles'] = '1';
    if ($search_list_descriptions) $params['search_list_descriptions'] = '1';
    if ($search_video_titles) $params['search_video_titles'] = '1';
    if ($search_video_descriptions) $params['search_video_descriptions'] = '1';
    
    return '?' . http_build_query($params);
}
?>

<div class="results-container">
    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php elseif (empty($results)): ?>
        <div class="no-results">
            <h3>No results found</h3>
            <p>Try adjusting your search criteria or clearing some filters.</p>
        </div>
    <?php else: ?>
        <?php if (empty($search_text) && empty($date_from) && empty($date_to) && 
                  empty($user_first_name) && empty($user_last_name) && 
                  empty($user_username) && empty($user_email)): ?>
            <div class="search-info">
                <p><em>Showing all available lists. Use the search form above to filter results.</em></p>
            </div>
        <?php endif; ?>
        <div class="results-summary">
            Showing <?php echo count($results); ?> of <?php echo $total_results; ?> results
            (Page <?php echo $page; ?> of <?php echo $total_pages; ?>)
        </div>
        
        <?php foreach ($results as $result): ?>
            <div class="result-item">
                <div class="result-header">
                    <h3 class="result-title">
                        <a href="spa.php#list/<?php echo $result['id']; ?>">
                            <?php echo htmlspecialchars($result['name']); ?>
                        </a>
                    </h3>
                    <div class="result-actions">
                        <a href="spa.php#list/<?php echo $result['id']; ?>" class="btn btn-primary">View List</a>
                        <?php if ($result['user_id'] != $current_user_id): ?>
                            <a href="spa.php#user-profile/<?php echo $result['user_id']; ?>" class="btn btn-secondary">View Profile</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="result-meta">
                    <strong>Created by:</strong> 
                    <?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?>
                    (@<?php echo htmlspecialchars($result['username']); ?>)
                    • <strong>Created:</strong> <?php echo formatDate($result['created_at']); ?>
                    <?php if ($result['updated_at'] != $result['created_at']): ?>
                        • <strong>Updated:</strong> <?php echo formatDate($result['updated_at']); ?>
                    <?php endif; ?>
                    • <strong>Visibility:</strong> <?php echo $result['is_public'] ? 'Public' : 'Private'; ?>
                </div>
                
                <?php if (!empty($result['description'])): ?>
                    <div class="result-description">
                        <?php echo htmlspecialchars($result['description']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="result-stats">
                    <span class="stat-item">
                        <strong><?php echo $result['video_count']; ?></strong> videos
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($has_prev): ?>
                    <a href="<?php echo buildPaginationUrl(1); ?>" class="btn btn-secondary">First</a>
                    <a href="<?php echo buildPaginationUrl($page - 1); ?>" class="btn btn-secondary">Previous</a>
                <?php else: ?>
                    <button disabled class="btn btn-secondary">First</button>
                    <button disabled class="btn btn-secondary">Previous</button>
                <?php endif; ?>
                
                <?php
                // Calculate page range to show
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <span>...</span>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo buildPaginationUrl($i); ?>" class="btn btn-secondary"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <span>...</span>
                <?php endif; ?>
                
                <?php if ($has_next): ?>
                    <a href="<?php echo buildPaginationUrl($page + 1); ?>" class="btn btn-secondary">Next</a>
                    <a href="<?php echo buildPaginationUrl($total_pages); ?>" class="btn btn-secondary">Last</a>
                <?php else: ?>
                    <button disabled class="btn btn-secondary">Next</button>
                    <button disabled class="btn btn-secondary">Last</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
