<?php
// Database configuration
define('DB_FILE', __DIR__ . '/todos.db');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
