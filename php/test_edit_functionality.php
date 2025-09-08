<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Edit Functionality Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Not logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit;
}

echo "<p style='color: green;'>✅ User is logged in (ID: " . $_SESSION['user_id'] . ")</p>";

try {
    require_once __DIR__ . '/db.php';
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Get user's lists
    $stmt = $pdo->prepare("SELECT * FROM user_lists WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $lists = $stmt->fetchAll();
    
    echo "<h2>Your Lists:</h2>";
    if (count($lists) > 0) {
        foreach ($lists as $list) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h3>" . htmlspecialchars($list['list_name']) . " (ID: " . $list['id'] . ")</h3>";
            echo "<p>Description: " . htmlspecialchars($list['description'] ?: 'No description') . "</p>";
            echo "<p>Public: " . ($list['is_public'] ? 'Yes' : 'No') . "</p>";
            echo "<p>Created: " . $list['created_at'] . "</p>";
            
            // Test edit functionality
            echo "<h4>Edit Test:</h4>";
            echo "<button onclick='testEditList(" . $list['id'] . ")'>Test Edit List</button>";
            echo "<div id='edit-result-" . $list['id'] . "'></div>";
            
            echo "</div>";
        }
    } else {
        echo "<p>No lists found. Create a list first.</p>";
    }
    
    // Test API endpoints
    echo "<h2>API Endpoint Tests:</h2>";
    if (count($lists) > 0) {
        $list_id = $lists[0]['id'];
        echo "<p><a href='api/get_list.php?id=$list_id' target='_blank'>Test Get List API</a></p>";
        echo "<p><a href='api/update_list.php?id=$list_id' target='_blank'>Test Update List API</a> (should show Method not allowed)</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>JavaScript Test:</h2>";
echo "<p>Open your browser's Developer Tools (F12) and check the Console tab for any JavaScript errors when trying to edit lists.</p>";

echo "<h2>Manual Edit Test:</h2>";
echo "<p>Try editing a list in the SPA and tell me:</p>";
echo "<ul>";
echo "<li>Does the edit form appear when you click 'Edit'?</li>";
echo "<li>Are there any JavaScript errors in the console?</li>";
echo "<li>What happens when you submit the edit form?</li>";
echo "</ul>";

echo "<p><a href='spa.php'>Go to SPA</a></p>";
?>

<script>
function testEditList(listId) {
    const resultDiv = document.getElementById('edit-result-' + listId);
    resultDiv.innerHTML = '<p>Testing edit for list ' + listId + '...</p>';
    
    // Test the API call
    fetch('api/get_list.php?id=' + listId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<p style="color: green;">✅ Get list API works - List: ' + data.list.list_name + '</p>';
            } else {
                resultDiv.innerHTML = '<p style="color: red;">❌ Get list API failed: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<p style="color: red;">❌ API call failed: ' + error.message + '</p>';
        });
}
</script>
