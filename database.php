<?php
require_once 'config.php';

class Database {
    private static $pdo = null;
    
    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO('sqlite:' . DB_FILE);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::initDatabase();
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
    
    private static function initDatabase() {
        $pdo = self::$pdo;
        
        // Create users table WITH is_admin column
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            is_admin INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS todos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            completed INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
    }
    
    public static function registerUser($username, $password) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $hashedPassword = Security::hashPassword($password);
        
        try {
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public static function loginUser($username, $password) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && Security::verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            return true;
        }
        return false;
    }
    
    public static function getTodos($userId) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM todos WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function addTodo($userId, $title, $description) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO todos (user_id, title, description) VALUES (:user_id, :title, :description)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':description' => $description
        ]);
    }
    
    public static function toggleTodo($todoId, $userId) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT completed FROM todos WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $todoId, ':user_id' => $userId]);
        $todo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$todo) {
            return false;
        }
        
        $newStatus = $todo['completed'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE todos SET completed = :completed WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            ':completed' => $newStatus,
            ':id' => $todoId,
            ':user_id' => $userId
        ]);
    }
    
    public static function deleteTodo($todoId, $userId) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("DELETE FROM todos WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            ':id' => $todoId,
            ':user_id' => $userId
        ]);
    }
    
    // ADMIN FUNCTIONS - NEW
    
    public static function isAdmin($userId) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user && $user['is_admin'] == 1;
    }
    
    public static function getAllUsers() {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT id, username, created_at, is_admin FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getAllTodos() {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT todos.*, users.username 
                             FROM todos 
                             JOIN users ON todos.user_id = users.id 
                             ORDER BY todos.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getUserStats() {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT 
                             (SELECT COUNT(*) FROM users) as total_users,
                             (SELECT COUNT(*) FROM todos) as total_todos,
                             (SELECT COUNT(*) FROM todos WHERE completed = 1) as completed_todos,
                             (SELECT COUNT(*) FROM todos WHERE completed = 0) as pending_todos");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function deleteUser($userId) {
        $pdo = self::getConnection();
        
        // Delete user's todos first
        $stmt = $pdo->prepare("DELETE FROM todos WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        
        // Delete user (but not if admin)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND is_admin = 0");
        return $stmt->execute([':id' => $userId]);
    }
}
?>
