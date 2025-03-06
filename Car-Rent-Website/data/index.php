<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - CARSrent</title>
    <link rel="icon" type="image/png" href="../images/image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <?php if (isset($error)): ?>
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
                    <h2>Welcome to <span>CARSrent</span></h2>
                    <p>Your journey begins with us. The best car rental experience awaits.</p>
                </div>
            </div>
            
            <!-- Right side - Login/Register forms -->
            <div class="form-content">
                <!-- Tabs for switching between login and register -->
                <div class="form-tabs">
                    <button class="tab-btn active" id="login-tab">Sign In</button>
                    <button class="tab-btn" id="register-tab">Sign Up</button>
                </div>
                
                <!-- Login Form -->
                <div class="form-panel active" id="login-panel">
                    <h2>Welcome Back!</h2>
                    <p class="form-subtitle">Sign in to continue your car rental experience</p>
                    
                    <form method="post" action="register.php" id="login-form">
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
                        
                        <button type="submit" name="signIn" class="submit-btn">Sign In</button>
                    </form>
                </div>
                
                <!-- Register Form -->
                <div class="form-panel" id="register-panel">
                    <h2>Create Account</h2>
                    <p class="form-subtitle">Join us and enjoy premium car rentals</p>
                    
                    <form method="post" action="register.php" id="register-form">
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
                        
                        <button type="submit" name="signUp" class="submit-btn">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation and tab switching
        document.addEventListener('DOMContentLoaded', function() {
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');
            const loginPanel = document.getElementById('login-panel');
            const registerPanel = document.getElementById('register-panel');
            
            // Tab switching
            loginTab.addEventListener('click', function() {
                loginTab.classList.add('active');
                registerTab.classList.remove('active');
                loginPanel.classList.add('active');
                registerPanel.classList.remove('active');
            });
            
            registerTab.addEventListener('click', function() {
                registerTab.classList.add('active');
                loginTab.classList.remove('active');
                registerPanel.classList.add('active');
                loginPanel.classList.remove('active');
            });
            
            // Form validation
            const registerForm = document.getElementById('register-form');
            registerForm.addEventListener('submit', function(e) {
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
        });
    </script>
</body>
</html>
