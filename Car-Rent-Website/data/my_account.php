<?php
session_start();
include("connect.php");

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Debug code to help identify image issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        SELECT s.*, c.id as car_id, c.name as car_name, c.brand, c.model, c.image,
               (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image,
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
            --primary-color: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #3730a3;
            --secondary-color: #06b6d4;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --background-color: #f9fafb;
            --surface-color: #ffffff;
            --surface-color-alt: #f3f4f6;
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-tertiary: #9ca3af;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius-sm: 8px;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --transition: all 0.3s ease;
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
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .brand span {
            color: var(--accent-color);
            transition: var(--transition);
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background-color: white;
            transition: var(--transition);
        }

        .nav-link:hover::after {
            width: 70%;
        }

        .nav-link:hover {
            transform: translateY(-2px);
        }

        .nav-link.active::after {
            width: 70%;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 7rem auto 2rem;
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
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            text-align: center;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            z-index: 0;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            border: 4px solid white;
            position: relative;
            z-index: 1;
            box-shadow: var(--shadow-md);
            background: white;
            margin-top: 30px;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-name {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        .profile-email {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .nav-tabs {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            background: var(--surface-color);
            border-radius: var(--border-radius-lg);
            padding: 1rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .nav-tab {
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-secondary);
        }

        .nav-tab i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-tab:hover {
            background: var(--surface-color-alt);
            color: var(--primary-color);
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
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-section {
            background: var(--surface-color);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            position: relative;
        }

        .info-section h2 {
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .info-section h2 i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .info-section p {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            background: var(--surface-color-alt);
            padding: 1.25rem;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }

        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .info-item strong {
            color: var(--text-secondary);
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-item span {
            font-weight: 500;
            color: var(--text-primary);
        }

        .order-container {
            display: grid;
            gap: 1.5rem;
        }

        .order-card {
            background: var(--surface-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 1.5rem;
            align-items: center;
            transition: var(--transition);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-color);
        }

        .order-image-container {
            width: 120px;
            height: 90px;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .order-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .order-card:hover .order-image {
            transform: scale(1.1);
        }

        .order-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .order-details h3 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        .order-meta {
            display: flex;
            gap: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .order-price {
            font-weight: 600;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .order-price i {
            font-size: 0.8rem;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .status-upcoming {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .status-preorder {
            background-color: rgba(6, 182, 212, 0.1);
            color: var(--secondary-color);
        }

        .status-completed {
            background: rgba(75, 85, 99, 0.1);
            color: var(--text-secondary);
        }

        .rental-dates {
            font-size: 0.9rem;
            color: var(--text-tertiary);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .rental-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rental-date i {
            color: var(--primary-color);
            font-size: 0.8rem;
        }

        .rental-date strong {
            font-weight: 600;
            color: var(--text-secondary);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: var(--danger-color);
            color: white;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .payment-methods-container {
            display: grid;
            gap: 1rem;
        }

        .payment-method-item {
            display: flex;
            align-items: center;
            padding: 1.25rem;
            background: var(--surface-color-alt);
            border-radius: var(--border-radius);
            margin-bottom: 0.75rem;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }

        .payment-method-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .payment-method-item p {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0;
            flex: 1;
        }

        .payment-method-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .payment-info {
            display: flex;
            flex-direction: column;
        }

        .payment-method-title {
            font-weight: 600;
            color: var(--text-primary);
        }

        .payment-method-details {
            font-size: 0.85rem;
            color: var(--text-tertiary);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert i {
            font-size: 1.25rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        /* Form styles */
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
            border-radius: var(--border-radius-sm);
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: var(--transition);
            background: var(--surface-color);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
            background: rgba(79, 70, 229, 0.1);
        }
        
        .edit-profile-toggle:hover {
            background: rgba(79, 70, 229, 0.2);
        }

        /* Mobile Responsiveness */
        @media (max-width: 992px) {
            .grid-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .nav-tabs {
                flex-direction: row;
                overflow-x: auto;
                padding: 0.5rem;
                white-space: nowrap;
            }

            .nav-tab {
                flex: 0 0 auto;
            }
        }

        @media (max-width: 768px) {
            .order-card {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 2rem 1.5rem;
            }

            .order-card::before {
                width: 100%;
                height: 5px;
                top: 0;
                left: 0;
            }

            .order-image-container {
                margin: 0 auto;
            }

            .order-meta {
                justify-content: center;
                margin-top: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .navbar {
                padding: 1rem;
            }

            .navbar-links {
                gap: 1rem;
            }

            .container {
                padding: 0 1rem;
            }

            .nav-link span {
                display: none;
            }

            .logout-btn span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="../index.php" class="brand">
            <span>CARS</span>rent
        </a>
        <div class="navbar-links">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-home"></i> <span>Home</span>
            </a>
            <a href="../book.php" class="nav-link">
                <i class="fas fa-car"></i> <span>Cars</span>
            </a>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if ($cancellationMessage): ?>
            <?php 
            $icon = strpos($cancellationMessage, 'success') !== false ? 'check-circle' : 'exclamation-triangle';
            echo str_replace('<div class="alert', '<div class="alert"><i class="fas fa-' . $icon . '"></i>', $cancellationMessage); 
            ?>
        <?php endif; ?>
        
        <?php if ($updateMessage): ?>
            <?php 
            $icon = strpos($updateMessage, 'success') !== false ? 'check-circle' : 'exclamation-triangle';
            echo str_replace('<div class="alert', '<div class="alert"><i class="fas fa-' . $icon . '"></i>', $updateMessage); 
            ?>
        <?php endif; ?>

        <div class="grid-container">
            <aside class="sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <img src="../images/profile-pic.png" alt="Profile Picture">
                    </div>
                    <h2 class="profile-name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                    <p class="profile-email"><?php echo htmlspecialchars($email); ?></p>
                </div>
                <div class="nav-tabs">
                    <div class="nav-tab" data-tab="edit-profile">
                        <i class="fas fa-user-edit"></i> Edit My Profile
                    </div>
                    <div class="nav-tab active" data-tab="orders">
                        <i class="fas fa-shopping-bag"></i> My Orders
                    </div>
                    <div class="nav-tab" data-tab="info">
                        <i class="fas fa-info-circle"></i> Account Info
                    </div>
                </div>
            </aside>

            <main class="tab-content">
                <div id="edit-profile">
                    <div class="info-section">
                        <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
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
                        <h2><i class="fas fa-shopping-bag"></i> My Orders</h2>
                        <?php if (empty($orderHistory)): ?>
                            <p>No orders found.</p>
                        <?php else: ?>
                            <div class="order-container">
                                <?php foreach ($orderHistory as $order): ?>
                                    <div class="order-card">
                                        <div class="order-image-container">
                                            <?php 
                                            // Debug: Output image path details
                                            echo "<!-- Debug Image Path Information -->";
                                            echo "<!-- Original image path: " . htmlspecialchars($order['image']) . " -->";
                                            
                                            // Try multiple approaches to find the correct image path
                                            $imagePath = '../images/car-placeholder.png'; // Default fallback
                                            
                                            if (!empty($order['image'])) {
                                                // Check if the path exists with and without the "../" prefix
                                                echo "<!-- Check path with '../' prefix: " . '../' . $order['image'] . " - Exists: " . (file_exists('../' . $order['image']) ? 'Yes' : 'No') . " -->";
                                                echo "<!-- Check path without prefix: " . $order['image'] . " - Exists: " . (file_exists($order['image']) ? 'Yes' : 'No') . " -->";
                                                
                                                if (file_exists('../' . $order['image'])) {
                                                    $imagePath = '../' . $order['image'];
                                                } else if (file_exists($order['image'])) {
                                                    $imagePath = $order['image'];
                                                }
                                                
                                                // Also try with the image path that might be stored as a relative path from Car-Rent-Website
                                                $altPath = str_replace('../', '', $order['image']);
                                                echo "<!-- Alt path: " . $altPath . " - Exists with '../': " . (file_exists('../' . $altPath) ? 'Yes' : 'No') . " -->";
                                                
                                                if (file_exists('../' . $altPath)) {
                                                    $imagePath = '../' . $altPath;
                                                }
                                                
                                                // Try car_images directory with car ID
                                                if (isset($order['car_id'])) {
                                                    $carImagesPath = "../images/cars/" . $order['car_id'] . "/";
                                                    echo "<!-- Car images directory: " . $carImagesPath . " - Exists: " . (is_dir($carImagesPath) ? 'Yes' : 'No') . " -->";
                                                    if (is_dir($carImagesPath)) {
                                                        $carImages = glob($carImagesPath . "*.{jpg,jpeg,png}", GLOB_BRACE);
                                                        if (!empty($carImages)) {
                                                            $imagePath = $carImages[0];
                                                            echo "<!-- Found car image: " . $imagePath . " -->";
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            echo "<!-- Final image path: " . htmlspecialchars($imagePath) . " -->";
                                            ?>
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($order['car_name']); ?>" class="order-image">
                                        </div>
                                        <div class="order-details">
                                            <h3><?php echo htmlspecialchars($order['car_name']); ?></h3>
                                            <div class="rental-dates">
                                                <div class="rental-date">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <span><strong>From:</strong> <?php echo date('M j, Y', strtotime($order['start_date'])); ?></span>
                                                </div>
                                                <div class="rental-date">
                                                    <i class="fas fa-calendar-check"></i>
                                                    <span><strong>To:</strong> <?php echo date('M j, Y', strtotime($order['end_date'])); ?></span>
                                                </div>
                                            </div>
                                            <div class="order-meta">
                                                <span class="order-price"><i class="fas fa-tag"></i> $<?php echo number_format($order['amount'], 2); ?></span>
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
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="info">
                    <div class="info-section">
                        <h2><i class="fas fa-user"></i> Personal Information</h2>
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
                        <h2><i class="fas fa-credit-card"></i> Payment Methods</h2>
                        <?php if (empty($savedPaymentMethods)): ?>
                            <p>No saved payment methods.</p>
                        <?php else: ?>
                            <div class="payment-methods-container">
                                <?php foreach ($savedPaymentMethods as $method): ?>
                                    <div class="payment-method-item">
                                        <?php 
                                        $details = json_decode($method['payment_details'], true);
                                        $icon = '';
                                        $displayInfo = '';
                                        $methodTitle = '';
                                        
                                        switch ($method['payment_type']) {
                                            case 'credit-card':
                                                $icon = 'fa-credit-card';
                                                $lastFour = substr($details['cardNumber'], -4);
                                                $displayInfo = "Card ending in {$lastFour}";
                                                $methodTitle = "Credit Card";
                                                break;
                                            case 'paypal':
                                                $icon = 'fa-paypal';
                                                $displayInfo = $details['email'];
                                                $methodTitle = "PayPal";
                                                break;
                                            case 'bank':
                                                $icon = 'fa-university';
                                                $lastFour = substr($details['accountNumber'], -4);
                                                $displayInfo = "Account ending in {$lastFour}";
                                                $methodTitle = "Bank Account";
                                                break;
                                        }
                                        ?>
                                        <p>
                                            <div class="payment-method-icon">
                                                <i class="fas <?php echo $icon; ?>"></i>
                                            </div>
                                            <div class="payment-info">
                                                <span class="payment-method-title"><?php echo $methodTitle; ?></span>
                                                <span class="payment-method-details"><?php echo htmlspecialchars($displayInfo); ?></span>
                                            </div>
                                        </p>
                                        <form method="post" action="delete_payment_method.php" style="margin-left: auto;">
                                            <input type="hidden" name="method_id" value="<?php echo $method['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this payment method?');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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
            
            // Add an animation to the alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>