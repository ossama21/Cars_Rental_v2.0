<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connect.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fName = trim($_POST['fName']);
    $lName = trim($_POST['lName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password, age, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'user')");
            $stmt->bind_param("sssssss", $fName, $lName, $email, $hashedPassword, $age, $phone, $address);
            
            if ($stmt->execute()) {
                $_SESSION['id'] = $stmt->insert_id;
                $_SESSION['firstName'] = $fName;
                $_SESSION['lastName'] = $lName;
                $_SESSION['email'] = $email;
                $_SESSION['age'] = $age;
                $_SESSION['phone'] = $phone;
                $_SESSION['address'] = $address;
                $_SESSION['role'] = 'user';
                header('Location: ../index.php');
                exit();
            } else {
                $error = "Registration failed. Please try again.";
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
    <title>Register - CARSrent</title>
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
                    <h2>Join <span>CARSrent</span></h2>
                    <p>Create your account and start your premium car rental journey today.</p>
                </div>
            </div>
            
            <!-- Right side - Register form -->
            <div class="form-content">
                <h2>Create Account</h2>
                <p class="form-subtitle">Join us and enjoy premium car rentals</p>
                
                <form method="post" action="" id="register-form">
                    <div class="input-group-container">
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" name="fName" placeholder="First Name" required>
                        </div>
                        
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" name="lName" placeholder="Last Name" required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    
                    <div class="input-group-container">
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <input type="number" name="age" placeholder="Age" min="18" max="100" required>
                        </div>
                        
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <input type="tel" name="phone" placeholder="Phone Number" required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <input type="text" name="address" placeholder="Address" required>
                    </div>
                    
                    <div class="input-group-container">
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" name="password" id="password" placeholder="Password" required>
                        </div>
                        
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
                        </div>
                    </div>
                    
                    <div class="terms-policy">
                        <label class="remember-me">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="#">Terms</a> and <a href="#">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="submit-btn">Create Account</button>

                    <div class="alternate-action">
                        Already have an account? <a href="login.php">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation
        document.getElementById('register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
            
            // Phone validation
            const phone = document.querySelector('input[name="phone"]').value;
            if (!/^\+?\d{10,}$/.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
            }
        });
    </script>
</body>
</html>
