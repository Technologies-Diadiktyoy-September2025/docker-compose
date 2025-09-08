<?php
session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
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
    
    // Execute main query
    $stmt = $pdo->prepare($main_query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // Calculate pagination info
    $total_pages = ceil($total_results / $per_page);
    $has_prev = $page > 1;
    $has_next = $page < $total_pages;
    
    // Format results
    $formatted_results = [];
    foreach ($results as $result) {
        $formatted_results[] = [
            'id' => $result['id'],
            'name' => $result['name'],
            'description' => $result['description'],
            'is_public' => (bool)$result['is_public'],
            'created_at' => $result['created_at'],
            'updated_at' => $result['updated_at'],
            'video_count' => (int)$result['video_count'],
            'user' => [
                'id' => $result['user_id'],
                'username' => $result['username'],
                'first_name' => $result['first_name'],
                'last_name' => $result['last_name'],
                'email' => $result['email']
            ]
        ];
    }
    
    echo json_encode([
        'success' => true,
        'results' => $formatted_results,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_results' => $total_results,
            'per_page' => $per_page,
            'has_prev' => $has_prev,
            'has_next' => $has_next
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
