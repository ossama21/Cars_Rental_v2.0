<?php
session_start();
include("connect.php");

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Retrieve user info
$email = $_SESSION['email'];
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : 'User';
$lastName = isset($_SESSION['lastName']) ? $_SESSION['lastName'] : '';
$age = isset($_SESSION['age']) ? $_SESSION['age'] : 'Not provided';
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : 'Not provided';
$address = isset($_SESSION['address']) ? $_SESSION['address'] : 'Not provided';

// Handle profile update
$updateMessage = '';
if (isset($_POST['update_profile'])) {
    $newFirstName = trim($_POST['firstName']);
    $newLastName = trim($_POST['lastName']);
    $newPhone = trim($_POST['phone']);
    $newAge = trim($_POST['age']);
    $newAddress = trim($_POST['address']);
    
    if (isset($_SESSION['id'])) {
        $userId = $_SESSION['id'];
        
        // Update in database
        $stmt = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, phone = ?, age = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $newFirstName, $newLastName, $newPhone, $newAge, $newAddress, $userId);
        
        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['firstName'] = $newFirstName;
            $_SESSION['lastName'] = $newLastName;
            $_SESSION['phone'] = $newPhone;
            $_SESSION['age'] = $newAge;
            $_SESSION['address'] = $newAddress;
            
            // Update local variables
            $firstName = $newFirstName;
            $lastName = $newLastName;
            $phone = $newPhone;
            $age = $newAge;
            $address = $newAddress;
            
            $updateMessage = '<div class="alert alert-success">Profile updated successfully!</div>';
        } else {
            $updateMessage = '<div class="alert alert-danger">Error updating profile: ' . $conn->error . '</div>';
        }
    }
}

// Get saved payment methods
$savedPaymentMethods = [];
if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $stmt = $conn->prepare("SELECT * FROM saved_payment_methods WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $savedPaymentMethods[] = $row;
    }
    $stmt->close();

    // Get order history with status
    $stmt = $conn->prepare("
        SELECT s.*, c.name as car_name, c.brand, c.model, c.image,
        CASE 
            WHEN s.status = 'preorder' THEN 'preorder'
            WHEN CURRENT_DATE < DATE(s.start_date) THEN 'upcoming'
            WHEN CURRENT_DATE BETWEEN DATE(s.start_date) AND DATE(s.end_date) THEN 'active'
            ELSE 'completed'
        END as rental_status,
        s.status as booking_status
        FROM services s 
        JOIN cars c ON s.car_id = c.id 
        WHERE s.email = ? 
        ORDER BY 
            CASE 
                WHEN s.status = 'preorder' THEN 1
                WHEN CURRENT_DATE < DATE(s.start_date) THEN 2
                WHEN CURRENT_DATE BETWEEN DATE(s.start_date) AND DATE(s.end_date) THEN 3
                ELSE 4
            END,
            s.start_date DESC
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $orderHistory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle order cancellation
$cancellationMessage = '';
if (isset($_POST['cancel_order'])) {
    $orderId = intval($_POST['order_id']);
    
    // Check if the order belongs to the user
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ? AND email = ?");
    $stmt->bind_param("is", $orderId, $email);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if ($order) {
        $startDate = new DateTime($order['start_date']);
        $now = new DateTime();
        
        // Allow cancellation if rental hasn't started
        if ($startDate > $now) {
            $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
            $stmt->bind_param("i", $orderId);
            if ($stmt->execute()) {
                $cancellationMessage = '<div class="alert alert-success">Order cancelled successfully.</div>';
                // Refresh order history with rental status
                $stmt = $conn->prepare("
                    SELECT s.*, c.name as car_name, c.brand, c.model, c.image,
                    CASE 
                        WHEN s.status = 'preorder' THEN 'preorder'
                        WHEN CURRENT_DATE < DATE(s.start_date) THEN 'upcoming'
                        WHEN CURRENT_DATE BETWEEN DATE(s.start_date) AND DATE(s.end_date) THEN 'active'
                        ELSE 'completed'
                    END as rental_status
                    FROM services s 
                    JOIN cars c ON s.car_id = c.id 
                    WHERE s.email = ? 
                    ORDER BY 
                        CASE 
                            WHEN s.status = 'preorder' THEN 1
                            WHEN CURRENT_DATE < DATE(s.start_date) THEN 2
                            WHEN CURRENT_DATE BETWEEN DATE(s.start_date) AND DATE(s.end_date) THEN 3
                            ELSE 4
                        END,
                        s.start_date DESC
                ");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $orderHistory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            } else {
                $cancellationMessage = '<div class="alert alert-danger">Failed to cancel order.</div>';
            }
        } else {
            $cancellationMessage = '<div class="alert alert-danger">Cannot cancel orders that have already started.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - CARSrent</title>
    <link rel="icon" type="image/png" href="../images/image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #3182ce;
            --secondary-color: #2c5282;
            --accent-color: #f6ad55;
            --success-color: #48bb78;
            --danger-color: #e53e3e;
            --warning-color: #ecc94b;
            --background-color: #f7fafc;
            --surface-color: #ffffff;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
        }

        .nav-link i {
            margin-right: 0.5rem;
        }

        .logout-btn {
            background: var(--danger-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: #c53030;
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 5rem auto 2rem;
            padding: 0 1.5rem;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .sidebar {
            position: sticky;
            top: 5rem;
            height: fit-content;
        }

        .profile-card {
            background: var(--surface-color);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            border: 4px solid var(--primary-color);
            padding: 4px;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .nav-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .nav-tab {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-tab.active {
            background: var(--primary-color);
            color: white;
        }

        .tab-content > div {
            display: none;
        }

        .tab-content > div.active {
            display: block;
        }

        .info-section {
            background: var(--surface-color);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            background: var(--background-color);
            padding: 1rem;
            border-radius: var(--border-radius);
        }

        .info-item strong {
            color: var(--text-secondary);
            display: block;
            margin-bottom: 0.5rem;
        }

        .order-card {
            background: var(--surface-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }

        .order-image {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-details h3 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .order-meta {
            display: flex;
            gap: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-active {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
        }

        .status-upcoming {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
            position: relative;
            overflow: hidden;
        }

        .status-upcoming::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(52, 152, 219, 0.2);
            animation: pulse 2s infinite;
        }

        .status-completed {
            background: rgba(149, 165, 166, 0.1);
            color: #7f8c8d;
        }

        .status-preorder {
            background-color: #3498db;
            color: white;
        }

        .status-preorder i {
            color: white;
        }

        .preorder-note {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .preorder-fee {
            color: #e67e22;
            font-weight: 500;
        }

        @keyframes pulse {
            0% { opacity: 0; }
            50% { opacity: 1; }
            100% { opacity: 0; }
        }

        .rental-dates {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .rental-dates strong {
            color: #2c3e50;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .payment-method-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--background-color);
            border-radius: var(--border-radius);
            margin-bottom: 0.75rem;
        }

        .payment-method-item p {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0;
            flex: 1;
        }

        .payment-method-item i {
            color: var(--primary-color);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(72, 187, 120, 0.1);
            color: var(--success-color);
        }

        .alert-danger {
            background: rgba(229, 62, 62, 0.1);
            color: var(--danger-color);
        }
        
        /* New styles for edit profile form */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .edit-profile-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
            cursor: pointer;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .edit-profile-toggle:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .order-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .order-image {
                margin: 0 auto;
            }

            .order-meta {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="../index.php" style="color: white; text-decoration: none;">
            <h1><span style="color: var(--accent-color);">CARS</span>rent</h1>
        </a>
        <div class="navbar-links">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="../book.php" class="nav-link">
                <i class="fas fa-car"></i> Cars
            </a>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if ($cancellationMessage): ?>
            <?php echo $cancellationMessage; ?>
        <?php endif; ?>
        
        <?php if ($updateMessage): ?>
            <?php echo $updateMessage; ?>
        <?php endif; ?>

        <div class="grid-container">
            <aside class="sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <img src="../images/profile-pic.png" alt="Profile Picture">
                    </div>
                    <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                    <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($email); ?></p>
                </div>
                <div class="nav-tabs">
                    <div class="nav-tab" data-tab="edit-profile">My Profile</div>
                    <div class="nav-tab active" data-tab="orders">My Orders</div>
                    <div class="nav-tab" data-tab="info">Account Info</div>
                </div>
            </aside>

            <main class="tab-content">
                <div id="edit-profile">
                    <div class="info-section">
                        <h2>Edit Profile</h2>
                        <p>Update your personal information below.</p>
                        
                        <form action="" method="post">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" 
                                           value="<?php echo htmlspecialchars($firstName); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" 
                                           value="<?php echo htmlspecialchars($lastName); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($phone); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="age">Age</label>
                                    <input type="number" class="form-control" id="age" name="age" min="18" max="100"
                                           value="<?php echo htmlspecialchars($age); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($address); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div id="orders" class="active">
                    <div class="info-section">
                        <h2>My Orders</h2>
                        <?php if (empty($orderHistory)): ?>
                            <p>No orders found.</p>
                        <?php else: ?>
                            <?php foreach ($orderHistory as $order): ?>
                                <div class="order-card">
                                    <img src="../<?php echo htmlspecialchars($order['image']); ?>" alt="<?php echo htmlspecialchars($order['car_name']); ?>" class="order-image">
                                    <div class="order-details">
                                        <h3><?php echo htmlspecialchars($order['car_name']); ?></h3>
                                        <p class="rental-dates">
                                            <strong>From:</strong> <?php echo date('M j, Y', strtotime($order['start_date'])); ?>
                                            <strong>To:</strong> <?php echo date('M j, Y', strtotime($order['end_date'])); ?>
                                        </p>
                                        <div class="order-meta">
                                            <span class="order-price">$<?php echo number_format($order['amount'], 2); ?></span>
                                            <div class="order-status status-<?php echo $order['rental_status']; ?>">
                                                <i class="fas fa-<?php 
                                                    echo $order['rental_status'] === 'preorder' ? 'clock' : 
                                                        ($order['rental_status'] === 'upcoming' ? 'calendar' : 
                                                        ($order['rental_status'] === 'active' ? 'car' : 'check')); 
                                                ?>"></i>
                                                <?php 
                                                    $status_text = $order['rental_status'] === 'preorder' ? 'Pre-ordered' : ucfirst($order['rental_status']);
                                                    echo $status_text; 
                                                ?>
                                            </div>
                                            
                                            <?php if ($order['rental_status'] === 'upcoming'): ?>
                                                <form method="post" style="display: inline-block;" 
                                                      onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <button type="submit" name="cancel_order" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Cancel Booking
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="info">
                    <div class="info-section">
                        <h2>Personal Information</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>Full Name</strong>
                                <span><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Email</strong>
                                <span><?php echo htmlspecialchars($email); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Phone</strong>
                                <span><?php echo htmlspecialchars($phone); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Age</strong>
                                <span><?php echo htmlspecialchars($age); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Address</strong>
                                <span><?php echo htmlspecialchars($address); ?></span>
                            </div>
                        </div>
                        
                        <div class="edit-profile-toggle" data-toggle="edit-profile">
                            <i class="fas fa-pencil-alt"></i> Edit Profile
                        </div>
                    </div>

                    <div class="info-section">
                        <h2>Saved Payment Methods</h2>
                        <?php if (empty($savedPaymentMethods)): ?>
                            <p>No saved payment methods.</p>
                        <?php else: ?>
                            <?php foreach ($savedPaymentMethods as $method): ?>
                                <div class="payment-method-item">
                                    <?php 
                                    $details = json_decode($method['payment_details'], true);
                                    $icon = '';
                                    $displayInfo = '';
                                    
                                    switch ($method['payment_type']) {
                                        case 'credit-card':
                                            $icon = 'fa-credit-card';
                                            $lastFour = substr($details['cardNumber'], -4);
                                            $displayInfo = "Card ending in {$lastFour}";
                                            break;
                                        case 'paypal':
                                            $icon = 'fa-paypal';
                                            $displayInfo = $details['email'];
                                            break;
                                        case 'bank':
                                            $icon = 'fa-university';
                                            $lastFour = substr($details['accountNumber'], -4);
                                            $displayInfo = "Account ending in {$lastFour}";
                                            break;
                                    }
                                    ?>
                                    <p>
                                        <i class="fas <?php echo $icon; ?>"></i>
                                        <?php echo htmlspecialchars($displayInfo); ?>
                                    </p>
                                    <form method="post" action="delete_payment_method.php" style="margin-left: auto;">
                                        <input type="hidden" name="method_id" value="<?php echo $method['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this payment method?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.nav-tab');
            const tabContents = document.querySelectorAll('.tab-content > div');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const targetId = tab.getAttribute('data-tab');

                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    // Update active content
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                        if (content.id === targetId) {
                            content.classList.add('active');
                        }
                    });
                });
            });
            
            // Add event listener for the edit profile toggle link
            const editProfileToggle = document.querySelector('.edit-profile-toggle');
            if (editProfileToggle) {
                editProfileToggle.addEventListener('click', () => {
                    // Find and click the "My Profile" tab
                    const profileTab = document.querySelector('.nav-tab[data-tab="edit-profile"]');
                    if (profileTab) {
                        profileTab.click();
                    }
                });
            }
        });
    </script>
</body>
</html>