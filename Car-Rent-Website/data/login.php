<?php
session_start();
include 'connect.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['firstName'] = $row['firstName'];
            $_SESSION['lastName'] = $row['lastName'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            
            if ($row['role'] === 'admin') {
                header("Location: ../admin/admin.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CARSrent</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand-name {
            font-size: 2rem;
            font-weight: 700;
            color: #3182ce;
        }
        .form-control {
            border-radius: 50px;
            padding: 12px 20px;
            margin-bottom: 20px;
        }
        .btn-login {
            border-radius: 50px;
            padding: 12px;
            background: #3182ce;
            border: none;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            background: #2c5282;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: 5px;
        }
        .signup-link {
            text-align: center;
            margin-top: 20px;
        }
        .signup-link a {
            color: #3182ce;
            text-decoration: none;
            font-weight: 600;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #3182ce;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <div class="brand-name">CARSRENT</div>
                <p>Welcome back! Please login to your account.</p>
            </div>
            
            <?php if($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-login">Login</button>
            </form>
            
            <div class="signup-link">
                Don't have an account? <a href="index.php">Sign Up</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>