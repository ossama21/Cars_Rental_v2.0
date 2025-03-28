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
            $updateMessage = '<div class="alert alert-danger">Failed to update profile: ' . $conn->error . '</div>';
        }
        $stmt->close();
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
                // Refresh order history
                $stmt = $conn->prepare("
                    SELECT s.*, c.name as car_name, c.brand, c.model, c.image 
                    FROM services s 
                    JOIN cars c ON s.car_id = c.id 
                    WHERE s.email = ? 
                    ORDER BY s.created_at DESC
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
    <title>My Profile - CARSrent</title>
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
        
        [data-theme="dark"] {
            --primary-color: #4299e1;
            --secondary-color: #3c6ea5;
            --accent-color: #f6ad55;
            --success-color: #68d391;
            --danger-color: #fc8181;
            --warning-color: #f6e05e;
            --background-color: #1a202c;
            --surface-color: #2d3748;
            --text-primary: #f7fafc;
            --text-secondary: #e2e8f0;
            --border-color: #4a5568;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.4);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.5);
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
            transition: background-color 0.3s ease, color 0.3s ease;
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
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
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
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
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
            transition: background-color 0.3s ease;
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
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
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
            transition: color 0.3s ease;
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

        [data-theme="dark"] .status-active {
            background: rgba(46, 204, 113, 0.2);
            color: #68d391;
        }

        .status-upcoming {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
            position: relative;
            overflow: hidden;
        }

        [data-theme="dark"] .status-upcoming {
            background: rgba(52, 152, 219, 0.2);
            color: #63b3ed;
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

        [data-theme="dark"] .status-completed {
            background: rgba(149, 165, 166, 0.2);
            color: #a0aec0;
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

        [data-theme="dark"] .preorder-note {
            color: #a0aec0;
        }

        .preorder-fee {
            color: #e67e22;
            font-weight: 500;
        }

        [data-theme="dark"] .preorder-fee {
            color: #ed8936;
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

        [data-theme="dark"] .rental-dates {
            color: #a0aec0;
        }

        .rental-dates strong {
            color: #2c3e50;
        }

        [data-theme="dark"] .rental-dates strong {
            color: #e2e8f0;
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

        [data-theme="dark"] .btn-danger:hover {
            background: #e53e3e;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .btn-secondary {
            background: var(--text-secondary);
            color: var(--surface-color);
        }

        .btn-secondary:hover {
            background: #2c5282;
        }

        .payment-method-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--background-color);
            border-radius: var(--border-radius);
            margin-bottom: 0.75rem;
            transition: background-color 0.3s ease;
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

        /* Form styles */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            color: var(--text-primary);
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2);
        }

        [data-theme="dark"] .form-control:focus {
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.4);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .edit-toggle {
            margin-top: 1rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Edit toggle button */
        .icon-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.25rem;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .icon-btn:hover {
            color: var(--primary-color);
        }

        .edit-form {
            display: none;
        }

        .edit-form.active {
            display: block;
        }

        .display-info {
            display: block;
        }

        .display-info.hidden {
            display: none;
        }

        /* Dark mode toggle */
        .theme-toggle {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--surface-color);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
            border: none;
            cursor: pointer;
            z-index: 999;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Pill for dark mode toggle */
        .nav-item-theme {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: var(--background-color);
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .nav-item-theme i {
            font-size: 1.25rem;
        }

        .nav-item-theme span {
            font-size: 0.875rem;
            font-weight: 500;
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

            .form-row {
                grid-template-columns: 1fr;
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
                    
                    <div class="nav-item-theme" id="theme-toggle">
                        <i class="fas fa-sun"></i>
                        <span>Light Mode</span>
                    </div>
                </div>
                <div class="nav-tabs">
                    <div class="nav-tab active" data-tab="orders">My Orders</div>
                    <div class="nav-tab" data-tab="info">My Info</div>
                </div>
            </aside>

            <main class="tab-content">
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
                                        <div class="order-meta
