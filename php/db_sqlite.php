<?php
// SQLite database configuration (alternative to MySQL)
$DB_FILE = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO("sqlite:$DB_FILE");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create users table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database connection failed: ' . $e->getMessage();
    exit;
}
?>
