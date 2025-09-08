<?php
echo "<h1>Server Test</h1>";
echo "<p>✅ PHP is working!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' . "</p>";
echo "<p>Script path: " . $_SERVER['SCRIPT_NAME'] ?? 'Unknown' . "</p>";
?>
