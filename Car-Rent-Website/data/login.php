<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connect.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['firstName'] = $user['firstName'];
            $_SESSION['lastName'] = $user['lastName'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['age'] = $user['age'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: ../admin/admin.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        }
    }
    $error = "Invalid email or password";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CARSrent</title>
    <link rel="icon" type="image/png" href="../images/image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="back-home">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>

        <div class="form-container">
            <!-- Left side - Car image and welcome content -->
            <div class="form-image">
                <img src="../images/img1.png" alt="Car Rental">
                <div class="overlay-text">
                    <h2>Welcome Back to <span>CARSrent</span></h2>
                    <p>Sign in to continue your premium car rental experience.</p>
                </div>
            </div>
            
            <!-- Right side - Login form -->
            <div class="form-content">
                <h2>Sign In</h2>
                <p class="form-subtitle">Welcome back! Please sign in to your account</p>
                
                <form method="post" action="" id="login-form">
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="submit-btn">Sign In</button>

                    <div class="alternate-action">
                        Don't have an account? <a href="register.php">Sign Up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>