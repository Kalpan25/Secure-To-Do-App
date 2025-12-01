<?php
require_once 'config.php';
require_once 'security.php';
require_once 'database.php';

Security::requireLogin();

$error = '';
$success = '';
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        if (isset($_POST['add_todo'])) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($title)) {
                $error = "Todo title is required.";
            } else {
                if (Database::addTodo($userId, $title, $description)) {
                    $success = "Todo added successfully!";
                } else {
                    $error = "Failed to add todo.";
                }
            }
        }
        
        if (isset($_POST['toggle_todo'])) {
            $todoId = $_POST['todo_id'] ?? 0;
            if (Database::toggleTodo($todoId, $userId)) {
                $success = "Todo status updated!";
            } else {
                $error = "Failed to update todo.";
            }
        }
        
        if (isset($_POST['delete_todo'])) {
            $todoId = $_POST['todo_id'] ?? 0;
            if (Database::deleteTodo($todoId, $userId)) {
                $success = "Todo deleted successfully!";
            } else {
                $error = "Failed to delete todo.";
            }
        }
    }
}

$todos = Database::getTodos($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure To-Do App</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="header">
                <h1>📝 My To-Do List</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo Security::escapeHTML($username); ?></strong></span>
                    <?php if (Database::isAdmin($_SESSION['user_id'])): ?>
                        <a href="admin.php" class="btn btn-primary">Admin Panel</a>
                    <?php endif; ?>
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
            
            <div class="add-todo-section">
                <h2>Add New To-Do</h2>
                <form method="POST" action="" class="add-todo-form">
                    <?php echo Security::getCSRFField(); ?>
                    
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            required 
                            placeholder="Enter todo title"
                            maxlength="200">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="3" 
                            placeholder="Add more details..."
                            maxlength="500"></textarea>
                    </div>
                    
                    <button type="submit" name="add_todo" class="btn btn-primary">Add To-Do</button>
                </form>
            </div>
            
            <div class="todo-list-section">
                <h2>Your To-Dos (<?php echo count($todos); ?>)</h2>
                
                <?php if (empty($todos)): ?>
                    <p class="no-todos">No todos yet! Add one above to get started.</p>
                <?php else: ?>
                    <div class="todo-list">
                        <?php foreach ($todos as $todo): ?>
                            <div class="todo-item <?php echo $todo['completed'] ? 'completed' : ''; ?>">
                                <div class="todo-content">
                                    <h3><?php echo Security::escapeHTML($todo['title']); ?></h3>
                                    <?php if (!empty($todo['description'])): ?>
                                        <p><?php echo Security::escapeHTML($todo['description']); ?></p>
                                    <?php endif; ?>
                                    <small class="todo-date">
                                        Created: <?php echo Security::escapeHTML($todo['created_at']); ?>
                                    </small>
                                </div>
                                
                                <div class="todo-actions">
                                    <form method="POST" action="" style="display:inline;">
                                        <?php echo Security::getCSRFField(); ?>
                                        <input type="hidden" name="todo_id" value="<?php echo $todo['id']; ?>">
                                        <button 
                                            type="submit" 
                                            name="toggle_todo" 
                                            class="btn btn-small btn-toggle"
                                            data-tooltip="<?php echo $todo['completed'] ? 'Mark as incomplete' : 'Mark as complete'; ?>">
                                            <?php echo $todo['completed'] ? '↩️ Undo' : '✓ Complete'; ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="" style="display:inline;">
                                        <?php echo Security::getCSRFField(); ?>
                                        <input type="hidden" name="todo_id" value="<?php echo $todo['id']; ?>">
                                        <button 
                                            type="submit" 
                                            name="delete_todo" 
                                            class="btn btn-small btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this todo?');"
                                            data-tooltip="Delete this todo">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    // Loading spinner for all forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                button.classList.add('loading');
            }
        });
    });
    </script>
</body>
</html>
