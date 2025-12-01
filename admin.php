<?php
require_once 'config.php';
require_once 'security.php';
require_once 'database.php';

Security::requireLogin();

// Check if user is admin
if (!Database::isAdmin($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$username = $_SESSION['username'];

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed.";
    } else {
        $userId = $_POST['user_id'] ?? 0;
        if (Database::deleteUser($userId)) {
            $success = "User and their todos deleted successfully!";
        } else {
            $error = "Failed to delete user. Cannot delete admin accounts.";
        }
    }
}

// Get all statistics
$stats = Database::getUserStats();
$allUsers = Database::getAllUsers();
$allTodos = Database::getAllTodos();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Secure To-Do App</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="header">
                <h1>👑 Admin Dashboard</h1>
                <div class="user-info">
                    <span>Admin: <strong><?php echo Security::escapeHTML($username); ?></strong></span>
                    <a href="dashboard.php" class="btn btn-secondary">My Todos</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo Security::escapeHTML($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo Security::escapeHTML($success); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Section -->
            <div class="stats-section">
                <h2>📊 System Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['total_todos']; ?></h3>
                        <p>Total Todos</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['completed_todos']; ?></h3>
                        <p>Completed</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['pending_todos']; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
            </div>
            
            <!-- All Users Section -->
            <div class="admin-section">
                <h2>👥 All Users (<?php echo count($allUsers); ?>)</h2>
                
                <?php if (empty($allUsers)): ?>
                    <p class="no-data">No users found.</p>
                <?php else: ?>
                    <div class="admin-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUsers as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo Security::escapeHTML($user['username']); ?></td>
                                        <td>
                                            <?php if ($user['is_admin']): ?>
                                                <span class="badge badge-admin">Admin</span>
                                            <?php else: ?>
                                                <span class="badge badge-user">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo Security::escapeHTML($user['created_at']); ?></td>
                                        <td>
                                            <?php if (!$user['is_admin']): ?>
                                                <form method="POST" action="" style="display:inline;">
                                                    <?php echo Security::getCSRFField(); ?>
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button 
                                                        type="submit" 
                                                        name="delete_user" 
                                                        class="btn btn-small btn-danger"
                                                        onclick="return confirm('Delete user and all their todos?');">
                                                        Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">Protected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- All Todos Section -->
            <div class="admin-section">
                <h2>📝 All Todos (<?php echo count($allTodos); ?>)</h2>
                
                <?php if (empty($allTodos)): ?>
                    <p class="no-data">No todos found.</p>
                <?php else: ?>
                    <div class="admin-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allTodos as $todo): ?>
                                    <tr>
                                        <td><?php echo $todo['id']; ?></td>
                                        <td><?php echo Security::escapeHTML($todo['username']); ?></td>
                                        <td><?php echo Security::escapeHTML($todo['title']); ?></td>
                                        <td><?php echo Security::escapeHTML($todo['description'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($todo['completed']): ?>
                                                <span class="badge badge-success">✓ Complete</span>
                                            <?php else: ?>
                                                <span class="badge badge-pending">○ Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo Security::escapeHTML($todo['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
