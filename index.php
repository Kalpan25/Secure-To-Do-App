<?php
require_once 'config.php';
require_once 'security.php';
require_once 'database.php';

$error = '';
$success = '';

if (Security::isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password.";
        } else {
            if (Database::loginUser($username, $password)) {
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure To-Do App - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Video Background -->
    <video id="video-background" autoplay muted loop playsinline>
        <source src="1.mp4" type="video/mp4">
    </video>
    
    <div class="container">
        <div class="auth-box">
            <h1>🔒 Secure To-Do App</h1>
            <h2>Login</h2>
            
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
            
            <form method="POST" action="" id="loginForm">
                <?php echo Security::getCSRFField(); ?>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autocomplete="username"
                        value="<?php echo Security::escapeHTML($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password">
                </div>
                
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>
            
            <p class="text-center">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
    
    <script>
    // Loading spinner on submit
    document.getElementById('loginForm').addEventListener('submit', function() {
        const button = this.querySelector('button[type="submit"]');
        button.classList.add('loading');
    });
    </script>
</body>
</html>
