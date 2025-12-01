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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($username) || empty($password) || empty($confirmPassword)) {
            $error = "All fields are required.";
        } elseif (strlen($username) < 3) {
            $error = "Username must be at least 3 characters long.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            $passwordError = Security::validatePassword($password);
            if ($passwordError) {
                $error = $passwordError;
            } else {
                if (Database::registerUser($username, $password)) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Username already exists. Please choose another.";
                }
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
    <title>Secure To-Do App - Register</title>
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
            <h2>Register</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo Security::escapeHTML($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo Security::escapeHTML($success); ?>
                    <p><a href="index.php">Go to Login</a></p>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form method="POST" action="" id="registerForm">
                <?php echo Security::getCSRFField(); ?>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        minlength="3"
                        autocomplete="username"
                        value="<?php echo Security::escapeHTML($_POST['username'] ?? ''); ?>">
                    <small>Minimum 3 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        minlength="8"
                        autocomplete="new-password">
                    <small>Minimum 8 characters, must include uppercase, lowercase, and number</small>
                    <div id="strength-bar"></div>
                    <small id="strength-text"></small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        minlength="8"
                        autocomplete="new-password">
                </div>
                
                <button type="submit" name="register" class="btn btn-primary">Register</button>
            </form>
            <?php endif; ?>
            
            <p class="text-center">
                Already have an account? <a href="index.php">Login here</a>
            </p>
        </div>
    </div>
    
    <script>
    // Password strength indicator
    document.getElementById('password').addEventListener('input', function(e) {
        const password = e.target.value;
        let strength = 0;
        let strengthText = '';
        
        if (password.length >= 8) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[a-z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;
        
        // Update or create strength bar
        let bar = document.getElementById('strength-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'strength-bar';
            bar.className = 'password-strength-bar';
            e.target.parentElement.appendChild(bar);
        }
        
        // Update strength text
        if (strength === 0) strengthText = '';
        else if (strength <= 25) strengthText = 'Weak';
        else if (strength <= 50) strengthText = 'Fair';
        else if (strength <= 75) strengthText = 'Good';
        else strengthText = 'Strong';
        
        document.getElementById('strength-text').textContent = strengthText;
        document.getElementById('strength-text').style.color = 
            strength < 50 ? '#dc3545' : strength < 75 ? '#ffc107' : '#28a745';
        
        bar.style.width = strength + '%';
        bar.style.backgroundColor = 
            strength < 50 ? '#dc3545' : strength < 75 ? '#ffc107' : '#28a745';
    });
    
    // Loading spinner on submit
    document.getElementById('registerForm').addEventListener('submit', function() {
        const button = this.querySelector('button[type="submit"]');
        button.classList.add('loading');
    });
    </script>
</body>
</html>
