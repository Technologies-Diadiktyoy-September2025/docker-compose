<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/youtube_config.php';

echo "<h1>Detailed OAuth Debug</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ User not logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p>✅ User ID: " . $user_id . "</p>";

// Check GET parameters
echo "<h2>GET Parameters:</h2>";
if (empty($_GET)) {
    echo "<p>No GET parameters</p>";
} else {
    echo "<pre>" . print_r($_GET, true) . "</pre>";
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    echo "<p>✅ Authorization code received: " . substr($code, 0, 20) . "...</p>";
    
    // Test the exchangeCodeForToken function step by step
    echo "<h2>Step 1: Testing exchangeCodeForToken function</h2>";
    
    try {
        echo "<p>Calling exchangeCodeForToken with code...</p>";
        $tokenData = exchangeCodeForToken($code);
        
        if ($tokenData === false) {
            echo "<p>❌ exchangeCodeForToken returned false</p>";
        } elseif ($tokenData === null) {
            echo "<p>❌ exchangeCodeForToken returned null</p>";
        } else {
            echo "<p>✅ exchangeCodeForToken returned data</p>";
            echo "<h3>Token Data:</h3>";
            echo "<pre>" . print_r($tokenData, true) . "</pre>";
            
            // Check if access_token exists
            if (isset($tokenData['access_token'])) {
                echo "<p>✅ access_token found</p>";
                
                // Test database insertion
                echo "<h2>Step 2: Testing database insertion</h2>";
                
                try {
                    $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
                    echo "<p>Calculated expiration: " . $expiresAt . "</p>";
                    
                    // Test the SQL query
                    $sql = '
                        INSERT INTO youtube_credentials (user_id, access_token, refresh_token, expires_at) 
                        VALUES (:user_id, :access_token, :refresh_token, :expires_at)
                        ON DUPLICATE KEY UPDATE 
                        access_token = VALUES(access_token),
                        refresh_token = VALUES(refresh_token),
                        expires_at = VALUES(expires_at),
                        updated_at = CURRENT_TIMESTAMP
                    ';
                    
                    echo "<p>SQL Query:</p>";
                    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
                    
                    $stmt = $pdo->prepare($sql);
                    echo "<p>✅ SQL prepared successfully</p>";
                    
                    $params = [
                        ':user_id' => $user_id,
                        ':access_token' => $tokenData['access_token'],
                        ':refresh_token' => $tokenData['refresh_token'] ?? null,
                        ':expires_at' => $expiresAt
                    ];
                    
                    echo "<p>Parameters:</p>";
                    echo "<pre>" . print_r($params, true) . "</pre>";
                    
                    $result = $stmt->execute($params);
                    
                    if ($result) {
                        echo "<p>✅ Database insertion successful!</p>";
                        
                        // Check if record was inserted
                        $checkStmt = $pdo->prepare('SELECT * FROM youtube_credentials WHERE user_id = :user_id');
                        $checkStmt->execute([':user_id' => $user_id]);
                        $record = $checkStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($record) {
                            echo "<p>✅ Record found in database:</p>";
                            echo "<pre>" . print_r($record, true) . "</pre>";
                        } else {
                            echo "<p>❌ Record not found in database after insertion</p>";
                        }
                        
                    } else {
                        echo "<p>❌ Database insertion failed</p>";
                        echo "<p>Error info: " . print_r($stmt->errorInfo(), true) . "</p>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
                    echo "<p>Error code: " . $e->getCode() . "</p>";
                }
                
            } else {
                echo "<p>❌ access_token not found in token data</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ exchangeCodeForToken error: " . $e->getMessage() . "</p>";
        echo "<p>Error code: " . $e->getCode() . "</p>";
        echo "<p>Stack trace:</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} elseif (isset($_GET['error'])) {
    echo "<p>❌ OAuth Error: " . $_GET['error'] . "</p>";
    if (isset($_GET['error_description'])) {
        echo "<p>Description: " . $_GET['error_description'] . "</p>";
    }
} else {
    echo "<p>❌ No authorization code or error received</p>";
}

echo "<h2>Session Data:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Server Info:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>cURL Available: " . (function_exists('curl_init') ? 'Yes' : 'No') . "</p>";
?>
