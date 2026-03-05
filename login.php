<?php
require_once 'config.php';
require_once 'functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        if (loginUser($email, $password)) {
            $redirect = $_GET['redirect'] ?? 'index.php';
            header("Location: $redirect");
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    } elseif (isset($_POST['register'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = 'Email already registered';
            } else {
                if (registerUser($name, $email, $password)) {
                    $success = 'Registration successful! Please login.';
                } else {
                    $error = 'Registration failed. Please try again.';
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
    <title>Login - Airbnb Clone</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; line-height: 1.6; color: #333; background-color: #ffffff; }
        
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .auth-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        
        .auth-header {
            padding: 40px 40px 20px;
            text-align: center;
        }
        
        .auth-header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            color: #FF385C;
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: #666;
            font-size: 14px;
        }
        
        .auth-body {
            padding: 0 40px 40px;
        }
        
        .auth-tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
        }
        
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: none;
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .auth-tab.active {
            color: #FF385C;
            border-bottom: 2px solid #FF385C;
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #FF385C;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: #FF385C;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #E31C5F;
        }
        
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        
        .auth-footer a {
            color: #FF385C;
            text-decoration: none;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" style="text-decoration: none;">
                    <h1><i class="fas fa-airbnb"></i> Airbnb</h1>
                </a>
                <p>Find your perfect getaway</p>
            </div>
            
            <div class="auth-body">
                <?php if($error): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="message success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <div class="auth-tabs">
                    <button class="auth-tab active" onclick="showForm('login')">Login</button>
                    <button class="auth-tab" onclick="showForm('register')">Register</button>
                </div>
                
                <form id="loginForm" class="auth-form active" method="POST">
                    <input type="hidden" name="login" value="1">
                    
                    <div class="form-group">
                        <label for="loginEmail">Email</label>
                        <input type="email" id="loginEmail" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" id="loginPassword" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                
                <form id="registerForm" class="auth-form" method="POST">
                    <input type="hidden" name="register" value="1">
                    
                    <div class="form-group">
                        <label for="registerName">Full Name</label>
                        <input type="text" id="registerName" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="registerEmail">Email</label>
                        <input type="email" id="registerEmail" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="registerPassword">Password</label>
                        <input type="password" id="registerPassword" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="registerConfirmPassword">Confirm Password</label>
                        <input type="password" id="registerConfirmPassword" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                
                <div class="auth-footer">
                    <p>By continuing, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></p>
                    <p style="margin-top: 10px;">
                        <a href="index.php"><i class="fas fa-arrow-left"></i> Back to home</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showForm(formType) {
            // Update tabs
            document.querySelectorAll('.auth-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Show selected form
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
            });
            document.getElementById(formType + 'Form').classList.add('active');
        }
        
        // Clear messages when switching forms
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.message').forEach(msg => {
                    msg.style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>
