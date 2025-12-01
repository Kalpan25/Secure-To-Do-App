<?php
require_once 'config.php';
require_once 'security.php';
require_once 'database.php';

// Initialize database connection (creates tables)
Database::getConnection();



if (Database::registerUser($username, $password)) {
    echo "✅ Test account created successfully!\n";
    
} else {
    echo "Test account already exists or creation failed.\n";
}
?>
