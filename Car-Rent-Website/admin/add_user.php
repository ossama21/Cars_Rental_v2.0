<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Use the auth function to check admin access
checkAdminAccess();

$email = $_SESSION['email'];
// Get admin info for display
$sql = "SELECT role, firstName, lastName FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Create a display name from firstName and lastName
$displayName = ($user) ? $user['firstName'] . ' ' . $user['lastName'] : $email;

// Initialize variables
$errors = [];
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $userEmail = trim($_POST['email']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    
    // Validate required fields
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";
    if (empty($userEmail)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    $checkEmailSql = "SELECT id FROM users WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("s", $userEmail);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();
    
    if ($checkEmailResult->num_rows > 0) {
        $errors[] = "Email address already exists";
    }
    
    // If no errors, proceed with database insertion
    if (empty($errors)) {
        // Hash the password (using MD5 as per your existing system)
        $hashedPassword = md5($password);
        
        // Insert new user
        $sqlInsert = "INSERT INTO users (firstName, lastName, email, password, role) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($sqlInsert);
        $insertStmt->bind_param("sssss", $firstName, $lastName, $userEmail, $hashedPassword, $role);
        
        if ($insertStmt->execute()) {
            $success = "User created successfully!";
            // Clear form data
            $firstName = $lastName = $userEmail = $password = $confirmPassword = "";
            $role = "user"; // Default role
        } else {
            $errors[] = "Error: " . $insertStmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/modern.css">
    
    <!-- Add inline CSS for this specific page -->
    <style>
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: #2d3748;
            color: #fff;
            transition: all 0.3s;
            position: fixed;
            height: 100%;
            z-index: 1000;
        }
        
        .admin-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .admin-logo {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu-item {
            margin-bottom: 5px;
        }
        
        .sidebar-menu-link {
            padding: 10px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        
        .sidebar-menu-link:hover, .sidebar-menu-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        
        .admin-main {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .form-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .form-body {
            padding: 30px;
        }
        
        .field-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .required-asterisk {
            color: #e53e3e;
            margin-left: 3px;
        }
        
        .form-hint {
            font-size: 0.8rem;
            color: #718096;
            margin-top: 5px;
        }
        
        .user-avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #a0aec0;
            margin: 0 auto 20px;
        }
        
        .role-badge {
            padding: 10px 15px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            margin-right: 10px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .role-badge.admin {
            background-color: rgba(237, 100, 100, 0.1);
            color: #e53e3e;
        }
        
        .role-badge.user {
            background-color: rgba(72, 187, 120, 0.1);
            color: #48bb78;
        }
        
        .role-badge.selected {
            border-color: currentColor;
        }
        
        .password-toggle {
            cursor: pointer;
        }
        
        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
        }
        
        @media (max-width: 992px) {
            .admin-sidebar {
                left: -250px;
            }
            
            .admin-sidebar.show {
                left: 0;
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper" id="adminWrapper">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <a href="admin.php" class="admin-logo">
                    <i class="fas fa-car"></i>
                    <span>CarRental</span> Admin
                </a>
            </div>
            
            <nav class="admin-sidebar-menu">
                <div class="sidebar-menu-item">
                    <a href="admin.php" class="sidebar-menu-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_cars.php" class="sidebar-menu-link">
                        <i class="fas fa-car"></i>
                        <span>Manage Cars</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_users.php" class="sidebar-menu-link active">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_payments.php" class="sidebar-menu-link">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Manage Payments</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link has-submenu" id="bookingsMenu">
                        <i class="fas fa-calendar-check"></i>
                        <span>Bookings</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link has-submenu" id="reportsMenu">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item mt-4">
                    <a href="../data/logout.php" class="sidebar-menu-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="admin-user">
                    <div class="admin-user-info">
                        <div class="admin-user-name"><?php echo htmlspecialchars($displayName); ?></div>
                        <div class="admin-user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                    <div class="admin-user-avatar">
                        <img src="../images/profile-pic.png" alt="Admin">
                    </div>
                </div>
            </div>
            
            <div class="admin-content">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0"><i class="fas fa-user-plus me-2"></i>Add New User</h1>
                        <p class="text-muted">Create a new user account</p>
                    </div>
                    <div>
                        <a href="manage_users.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Users
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Add User Form -->
                <div class="form-card">
                    <div class="form-header">
                        <h5 class="mb-0">User Information</h5>
                    </div>
                    <div class="form-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-4 mb-4 mb-md-0 text-center">
                                    <div class="user-avatar-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    
                                    <div class="text-center mb-4">
                                        <h5>New User Account</h5>
                                        <p class="text-muted small">Auto-assigned avatar based on user's initials</p>
                                    </div>
                                    
                                    <div class="text-start">
                                        <div class="field-group">
                                            <label class="form-label">User Role</label>
                                            <div>
                                                <label class="role-badge admin <?php echo isset($role) && $role === 'admin' ? 'selected' : ''; ?>">
                                                    <input type="radio" name="role" value="admin" <?php echo isset($role) && $role === 'admin' ? 'checked' : ''; ?> class="d-none">
                                                    <i class="fas fa-user-shield"></i> Admin
                                                </label>
                                                <label class="role-badge user <?php echo !isset($role) || $role === 'user' ? 'selected' : ''; ?>">
                                                    <input type="radio" name="role" value="user" <?php echo !isset($role) || $role === 'user' ? 'checked' : ''; ?> class="d-none">
                                                    <i class="fas fa-user"></i> User
                                                </label>
                                            </div>
                                            <div class="form-hint">Select the user's role and permissions level</div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mt-4" role="alert">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-info-circle"></i>
                                            </div>
                                            <div class="text-start">
                                                <p class="mb-0 small">
                                                    Admin users have full access to the admin dashboard and all management functions.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column -->
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="firstName" class="form-label">First Name <span class="required-asterisk">*</span></label>
                                                <input type="text" class="form-control" id="firstName" name="firstName" 
                                                       value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="lastName" class="form-label">Last Name <span class="required-asterisk">*</span></label>
                                                <input type="text" class="form-control" id="lastName" name="lastName" 
                                                       value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="field-group">
                                        <label for="email" class="form-label">Email Address <span class="required-asterisk">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($userEmail) ? htmlspecialchars($userEmail) : ''; ?>" required>
                                        <div class="form-hint">This will be used as the username for login</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="password" class="form-label">Password <span class="required-asterisk">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="password" name="password" required>
                                                    <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="password-strength bg-secondary w-100" id="passwordStrength"></div>
                                                <div class="form-hint">Minimum 6 characters</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="confirmPassword" class="form-label">Confirm Password <span class="required-asterisk">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                                    <button class="btn btn-outline-secondary password-toggle" type="button" data-target="confirmPassword">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-warning" role="alert">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1">Password Security</h5>
                                                <p class="mb-0 small">
                                                    Create a strong password that includes uppercase and lowercase letters,
                                                    numbers, and special characters for better security.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="manage_users.php" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="fas fa-user-plus me-2"></i>Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle sidebar functionality
        const toggleSidebar = document.getElementById('toggleSidebar');
        const adminWrapper = document.getElementById('adminWrapper');
        const adminSidebar = document.getElementById('adminSidebar');
        
        toggleSidebar.addEventListener('click', function() {
            adminWrapper.classList.toggle('sidebar-collapsed');
            if (window.innerWidth < 992) {
                adminSidebar.classList.toggle('show');
            }
        });
        
        // Password visibility toggle
        const passwordToggles = document.querySelectorAll('.password-toggle');
        
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordField = document.getElementById(targetId);
                
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // Toggle icon
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        });
        
        // Role badge selection
        const roleBadges = document.querySelectorAll('.role-badge');
        
        roleBadges.forEach(badge => {
            badge.addEventListener('click', function() {
                roleBadges.forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        // Simple password strength meter
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('passwordStrength');
        
        passwordInput.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            
            // Length check
            if (value.length >= 6) strength += 25;
            
            // Contains lowercase
            if (/[a-z]/.test(value)) strength += 25;
            
            // Contains uppercase
            if (/[A-Z]/.test(value)) strength += 25;
            
            // Contains number or special char
            if (/[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) strength += 25;
            
            // Update strength meter
            strengthMeter.style.width = `${strength}%`;
            
            if (strength <= 25) {
                strengthMeter.style.backgroundColor = '#e53e3e'; // Weak
            } else if (strength <= 50) {
                strengthMeter.style.backgroundColor = '#ed8936'; // Fair
            } else if (strength <= 75) {
                strengthMeter.style.backgroundColor = '#ecc94b'; // Good
            } else {
                strengthMeter.style.backgroundColor = '#48bb78'; // Strong
            }
        });
        
        // Password confirmation check
        const confirmPasswordInput = document.getElementById('confirmPassword');
        
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value === passwordInput.value) {
                this.style.borderColor = '#48bb78';
            } else {
                this.style.borderColor = '#e53e3e';
            }
        });
    </script>
</body>
</html>