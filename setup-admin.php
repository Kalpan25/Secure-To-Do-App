<?php
require_once 'config.php';
require_once 'security.php';
require_once 'database.php';

// Delete old database to add is_admin column
if (file_exists('todos.db')) {
    unlink('todos.db');
    echo "🗑️  Old database deleted.\n";
}

// Initialize database with new schema
Database::getConnection();
echo "✅ Database initialized with admin support.\n\n";

// Create admin account manually
$pdo = Database::getConnection();
$username = 'admin';
$password = 'Admin123!';
$hashedPassword = Security::hashPassword($password);

$stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (:username, :password, 1)");
$stmt->execute([
    ':username' => $username,
    ':password' => $hashedPassword
]);

echo "👑 Admin account created successfully!\n";
echo "Username: admin\n";
echo "Password: Admin123!\n\n";
echo "Navigate to http://localhost:8000/admin.php after logging in!\n";
?>
