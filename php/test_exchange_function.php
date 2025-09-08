<?php
require_once __DIR__ . '/youtube_config.php';

echo "<h1>Test exchangeCodeForToken Function</h1>";

echo "<h2>Configuration Check:</h2>";
echo "<p><strong>Client ID:</strong> " . YOUTUBE_CLIENT_ID . "</p>";
echo "<p><strong>Client Secret:</strong> " . (strlen(YOUTUBE_CLIENT_SECRET) > 0 ? 'Set (' . strlen(YOUTUBE_CLIENT_SECRET) . ' chars)' : 'Not set') . "</p>";
echo "<p><strong>Redirect URI:</strong> " . YOUTUBE_REDIRECT_URI . "</p>";
echo "<p><strong>Token URL:</strong> " . YOUTUBE_TOKEN_URL . "</p>";

echo "<h2>Test cURL:</h2>";
if (function_exists('curl_init')) {
    echo "<p>✅ cURL is available</p>";
    
    // Test basic connectivity
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<p>❌ cURL error: " . $error . "</p>";
    } else {
        echo "<p>✅ Can reach Google OAuth endpoint (HTTP " . $httpCode . ")</p>";
    }
} else {
    echo "<p>❌ cURL is not available</p>";
}

echo "<h2>Test with Invalid Code:</h2>";
try {
    $result = exchangeCodeForToken('invalid_code_test');
    if ($result === false) {
        echo "<p>✅ Function correctly returns false for invalid code</p>";
    } else {
        echo "<p>❌ Function should return false for invalid code, got:</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p>❌ Exception with invalid code: " . $e->getMessage() . "</p>";
}

echo "<h2>Instructions:</h2>";
echo "<ol>";
echo "<li>Go to <a href='youtube_connect.php'>youtube_connect.php</a></li>";
echo "<li>Click 'Connect YouTube Account'</li>";
echo "<li>Complete OAuth flow</li>";
echo "<li>Copy the 'code' parameter from the callback URL</li>";
echo "<li>Paste it below to test</li>";
echo "</ol>";

if (isset($_POST['test_code']) && !empty($_POST['test_code'])) {
    $code = $_POST['test_code'];
    echo "<h2>Testing with Real Code:</h2>";
    echo "<p>Code: " . substr($code, 0, 20) . "...</p>";
    
    try {
        $tokenData = exchangeCodeForToken($code);
        if ($tokenData) {
            echo "<p>✅ Token exchange successful!</p>";
            echo "<h3>Response:</h3>";
            echo "<pre>" . print_r($tokenData, true) . "</pre>";
            
            // Check required fields
            $required = ['access_token', 'expires_in'];
            foreach ($required as $field) {
                if (isset($tokenData[$field])) {
                    echo "<p>✅ " . $field . ": " . $tokenData[$field] . "</p>";
                } else {
                    echo "<p>❌ Missing " . $field . "</p>";
                }
            }
        } else {
            echo "<p>❌ Token exchange failed (returned false/null)</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Exception: " . $e->getMessage() . "</p>";
        echo "<p>Stack trace:</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>

<form method="post" style="margin-top: 20px;">
    <label for="test_code"><strong>Authorization Code:</strong></label><br>
    <input type="text" id="test_code" name="test_code" style="width: 500px; padding: 5px;" placeholder="Paste the authorization code here">
    <br><br>
    <button type="submit" style="padding: 10px 20px;">Test Token Exchange</button>
</form>
