<?php
session_start();
$_SESSION['user_id'] = 1;
$_GET['q'] = 'test';

echo "Testing search API from XAMPP directory...\n";

try {
    ob_start();
    include 'api/search_profiles.php';
    $output = ob_get_clean();
    echo "API Response: " . $output . "\n";
} catch (Exception $e) {
    echo "API Error: " . $e->getMessage() . "\n";
}
?>
