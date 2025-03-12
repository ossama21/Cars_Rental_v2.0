<?php
session_start();
if (!isset($_SESSION['firstName'])) {
    header('Location: data/index.php');
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "car_rent";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$car = null;
$is_preorder = isset($_GET['preorder']) && $_GET['preorder'] == '1';
$available_from = isset($_GET['available_from']) ? $_GET['available_from'] : null;

// Handle AJAX request for saved payment methods
if (isset($_GET['get_payment_methods'])) {
    // ... existing payment methods code ...
}

// Get car details with discount information
if (isset($_GET['car_id'])) {
    $stmt = $conn->prepare("
        SELECT c.*, 
            CASE 
                WHEN d.discount_type = 'percentage' THEN c.price * (1 - d.discount_value/100)
                WHEN d.discount_type = 'fixed' THEN c.price - d.discount_value
                ELSE c.price 
            END as discounted_price,
            CASE 
                WHEN d.discount_type = 'percentage' THEN CONCAT(d.discount_value, '%')
                WHEN d.discount_type = 'fixed' THEN CONCAT('$', d.discount_value)
                ELSE NULL 
            END as discount_display
        FROM cars c 
        LEFT JOIN car_discounts d ON c.id = d.car_id 
            AND CURRENT_TIMESTAMP BETWEEN d.start_date AND d.end_date 
            AND d.end_date > CURRENT_TIMESTAMP 
        WHERE c.id = ?");
    
    $car_id = intval($_GET['car_id']);
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
    $stmt->close();

    if (!$car) {
        die("Car not found");
    }

    // Override price with URL parameter if it exists (from discounted price)
    if (isset($_GET['price'])) {
        $car['price'] = floatval($_GET['price']);
    }
}

// Add isAdmin check
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Create services table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS `services` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(255) NOT NULL,
    `phone` varchar(50) NOT NULL,
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `duration` int(11) NOT NULL,
    `email` varchar(255) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `car_id` int(11) NOT NULL,
    `payment_method` varchar(50) NOT NULL,
    `payment_details` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `car_id` (`car_id`),
    CONSTRAINT `services_car_fk` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$conn->query($createTableSQL);

// Add status column to services table if it doesn't exist
$alterTableSQL = "ALTER TABLE services ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'upcoming'";
$conn->query($alterTableSQL);

// Create saved_payment_methods table if it doesn't exist
$createPaymentMethodsTableSQL = "CREATE TABLE IF NOT EXISTS `saved_payment_methods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `payment_type` varchar(50) NOT NULL,
    `payment_details` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$conn->query($createPaymentMethodsTableSQL);

// Get user data from session
$userData = [
    'firstName' => $_SESSION['firstName'] ?? '',
    'lastName' => $_SESSION['lastName'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'phone' => $_SESSION['phone'] ?? ''
];

// Process car_id and preorder parameters
$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;
$is_preorder = isset($_GET['preorder']) && $_GET['preorder'] == 1;
$available_from = isset($_GET['available_from']) ? $_GET['available_from'] : '';
$car = null;

if ($car_id > 0) {
    if ($is_preorder) {
        // For preorders, just get the car details
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
    } else {
        // For regular bookings, check availability
        $stmt = $conn->prepare("SELECT c.*, (
            SELECT COUNT(*) 
            FROM services s 
            WHERE s.car_id = c.id 
            AND (
                CURRENT_DATE BETWEEN DATE(s.start_date) AND DATE(s.end_date)
                OR DATE(s.start_date) >= CURRENT_DATE
            )
        ) as active_rentals
        FROM cars c WHERE c.id = ?");
    }
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $car = $result->fetch_assoc();
        if (!$is_preorder && $car['active_rentals'] >= $car['quantity']) {
            header("Location: book.php?error=car_unavailable");
            exit();
        }
    }
    $stmt->close();
}

// Check if it's an AJAX POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine if the request is AJAX or a direct form submission
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // For AJAX requests, set JSON header
    if ($isAjax) {
        header('Content-Type: application/json');
    }

    // Requesting car details via AJAX
    if (isset($_POST['car_id']) && !isset($_POST['submit_booking'])) {
        if ($isAjax) {
            $car_id = intval($_POST['car_id']);
            $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
            $stmt->bind_param("i", $car_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $car = $result->fetch_assoc();
                echo json_encode(['car' => $car]);
            } else {
                echo json_encode(['error' => 'Car not found']);
            }
            $stmt->close();
            exit;
        }
    }

    // Handle booking submission
    if ((isset($_POST['submit_booking']) || isset($_POST['payment_method'])) && isset($_POST['car_id'])) {
        try {
            $payment_method = $_POST['payment_method'] ?? 'unknown';
            $payment_data = $_POST['payment_data'] ?? '{}';
            $name = isset($_POST['name']) ? $_POST['name'] : ($_POST['firstName'] . ' ' . $_POST['lastName']);
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $car_id = intval($_POST['car_id']);
            $start_date = $_POST['startDate'] ?? $_POST['start_date'];
            $end_date = $_POST['endDate'] ?? $_POST['end_date'];
            
            // Get car details and check availability
            $stmt = $conn->prepare("SELECT c.*, (
                SELECT COUNT(*) 
                FROM services s 
                WHERE s.car_id = c.id 
                AND (
                    (? BETWEEN DATE(s.start_date) AND DATE(s.end_date))
                    OR (? BETWEEN DATE(s.start_date) AND DATE(s.end_date))
                    OR (DATE(s.start_date) BETWEEN ? AND ?)
                )
            ) as active_rentals
            FROM cars c WHERE c.id = ?");
            
            $stmt->bind_param("ssssi", $start_date, $end_date, $start_date, $end_date, $car_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $car = $result->fetch_assoc();
            $stmt->close();
            
            if (!$car) {
                throw new Exception('Car not found');
            }

            // Check if enough cars are available
            if ($car['active_rentals'] >= $car['quantity']) {
                throw new Exception('Sorry, this car is not available for the selected dates');
            }
            
            // Calculate duration and total amount
            $startDateObj = new DateTime($start_date);
            $endDateObj = new DateTime($end_date);
            $duration = max(1, $startDateObj->diff($endDateObj)->days);
            
            // Validate preorder conditions
            if (isset($_POST['is_preorder']) && $_POST['is_preorder'] == 1) {
                if ($duration < 3) {
                    throw new Exception('Pre-orders require a minimum rental duration of 3 days');
                }
                $totalAmount = ($duration * $car['price']) + 25 + 15; // Base price + Insurance + Preorder fee ($15)
            } else {
                $totalAmount = ($duration * $car['price']) + 25; // Base price + Insurance fee
            }

            // Store booking in session for confirmation page
            $_SESSION['booking'] = [
                'username' => $name,
                'phone' => $phone,
                'email' => $email,
                'startDate' => $start_date,
                'endDate' => $end_date,
                'duration' => $duration,
                'car' => $car,
                'paymentMethod' => $payment_method,
                'paymentDetails' => $payment_data,
                'totalAmount' => $totalAmount
            ];

            // Begin transaction
            $conn->begin_transaction();
            
            // Insert into services table with status
            $status = isset($_POST['is_preorder']) && $_POST['is_preorder'] == 1 ? 'preorder' : 'upcoming';
            $stmt = $conn->prepare("INSERT INTO services (
                username, phone, start_date, end_date, duration,
                email, amount, car_id, payment_method, payment_details, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param(
                "ssssisdisss",
                $name,
                $phone,
                $start_date,
                $end_date,
                $duration,
                $email,
                $totalAmount,
                $car_id,
                $payment_method,
                $payment_data,
                $status
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to save booking: " . $stmt->error);
            }
            
            $bookingId = $conn->insert_id;
            $_SESSION['booking']['bookingId'] = $bookingId;

            // Insert into payments table
            $stmt = $conn->prepare("INSERT INTO payments (
                booking_id, method, amount, status, transaction_id
            ) VALUES (?, ?, ?, 'completed', ?)");
            
            $transactionId = 'TXN' . time() . rand(1000, 9999);
            $stmt->bind_param("isds", $bookingId, $payment_method, $totalAmount, $transactionId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to record payment: " . $stmt->error);
            }

            // Save payment method if checkbox is checked
            if (isset($_POST['save_payment_info']) && $_POST['save_payment_info'] === 'on' && isset($_SESSION['id'])) {
                $userId = $_SESSION['id'];
                
                // Check if this payment method already exists
                $stmt = $conn->prepare("SELECT id FROM saved_payment_methods WHERE user_id = ? AND payment_type = ? AND payment_details = ?");
                $stmt->bind_param("iss", $userId, $payment_method, $payment_data);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    // Payment method doesn't exist, save it
                    $stmt = $conn->prepare("INSERT INTO saved_payment_methods (user_id, payment_type, payment_details) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $userId, $payment_method, $payment_data);
                    $stmt->execute();
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Booking successful', 'redirect' => 'confirmation.php']);
                exit;
            } else {
                // For traditional form submission, redirect to confirmation page
                header("Location: confirmation.php");
                exit;
            }
            
        } catch (Exception $e) {
            if ($conn && $conn->ping()) {
                $conn->rollback();
            }
            
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            } else {
                $_SESSION['booking_error'] = $e->getMessage();
                header("Location: checkout.php?car_id=" . $car_id . "&error=1");
                exit;
            }
        }
        exit;
    }
}

// Display any error from previous submission attempt
$error_message = '';
if (isset($_GET['error']) && isset($_SESSION['booking_error'])) {
    $error_message = $_SESSION['booking_error'];
    unset($_SESSION['booking_error']);
}

// Get saved payment methods for the user
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
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - CARSRENT</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="./images/image.png">
    <link rel="stylesheet" href="./css/modern.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --background-color: #f5f7fa;
            --text-color: #2c3e50;
            --border-radius: 12px;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        /* Navbar Styling */
        .navbar {
            background: var(--primary-color) !important;
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1030;
            transition: all 0.4s ease;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand, .nav-link {
            color: white !important;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .brand-highlight {
            color: var(--secondary-color);
        }

        .nav-list {
            display: flex;
            gap: 1.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
        }

        .nav-link:hover {
            color: var(--secondary-color) !important;
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0.5rem;
            transition: var(--transition);
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--secondary-color);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
        }

        .profile-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .profile-menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .profile-menu-item:hover {
            background: var(--background-color);
            color: var(--secondary-color);
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            gap: 6px;
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
        }

        .menu-toggle span {
            display: block;
            width: 25px;
            height: 2px;
            background: white;
            transition: var(--transition);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2.5rem;
            max-width: 1300px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .checkout-section {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .progress-step {
            position: relative;
            display: flex;
            gap: 1.5rem;
            padding: 2rem 0;
        }

        .progress-step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 17px;
            top: 4rem;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--background-color);
            border: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .step-number.active {
            background: var(--secondary-color);
            color: white;
            transform: scale(1.1);
        }

        .step-number.completed {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .step-content {
            flex: 1;
        }

        .step-content h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var (--text-color);
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            font-family: inherit;
            transition: var(--transition);
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .payment-method {
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }

        .payment-method::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--secondary-color);
            opacity: 0;
            transition: var(--transition);
        }

        .payment-method:hover::before,
        .payment-method.active::before {
            opacity: 1;
        }

        .payment-method img {
            height: 40px;
            width: auto;
            object-fit: contain;
            transition: var(--transition);
        }

        .payment-method:hover {
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .payment-method.active {
            border-color: var(--secondary-color);
            background: rgba(52, 152, 219, 0.05);
        }

        .payment-method:hover img,
        .payment-method.active img {
            transform: scale(1.1);
        }

        .payment-form {
            background: var(--background-color);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-top: 1.5rem;
            display: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .order-summary {
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .car-info {
            display: flex;
            gap: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .car-image {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: var (--border-radius);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-color);
        }

        .feature-item i {
            color: var(--secondary-color);
        }

        .rental-summary {
            background: var(--background-color);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin: 1.5rem 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            color: var(--text-color);
        }

        .total-row {
            border-top: 2px solid var(--primary-color);
            margin-top: 1rem;
            padding-top: 1rem;
            font-weight: 600;
            font-size: 1.2rem;
            color: var (--primary-color);
        }

        .btn-primary {
            background: var(--secondary-color);
            border: none;
            padding: 1rem 2rem;
            color: white;
            font-weight: 600;
            border-radius: var(--border-radius);
            width: 100%;
            font-size: 1.1rem;
            margin-top: 2rem;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .secure-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--success-color);
            font-size: 0.9rem;
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(46, 204, 113, 0.1);
            border-radius: var(--border-radius);
        }

        .alert {
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        @media (max-width: 992px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .payment-methods {
                grid-template-columns: 1fr;
            }

            .checkout-section {
                padding: 1.5rem;
            }
        }

        /* Add these styles after the existing styles in the style tag */

        /* Step Navigation and Validation */
        .progress-step.active .step-content {
            animation: fadeIn 0.5s ease;
        }

        .step-buttons {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-outline-secondary {
            background: transparent;
            border: 2px solid var(--text-color);
            color: var(--text-color);
            padding: 0.875rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-outline-secondary:hover {
            background: var(--text-color);
            color: white;
            transform: translateY(-2px);
        }

        .form-control.invalid {
            border-color: var(--accent-color);
            animation: shake 0.5s ease;
        }

        .error-message {
            color: var(--accent-color);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-message::before {
            content: '\f071';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        /* Animation Keyframes */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Loading State */
        .btn-primary.loading {
            position: relative;
            color: transparent;
            pointer-events: none;
        }

        .btn-primary.loading::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 1.5rem;
            height: 1.5rem;
            border: 2px solid white;
            border-radius: 50%;
            border-right-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from { transform: translate(-50%, -50%) rotate(0deg); }
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Payment Method Improvements */
        .payment-method {
            position: relative;
            overflow: hidden;
        }

        .payment-method::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            color: var(--success-color);
            opacity: 0;
            transform: scale(0);
            transition: var(--transition);
        }

        .payment-method.active::after {
            opacity: 1;
            transform: scale(1);
        }

        /* Summary Section Enhancements */
        .car-info {
            position: relative;
            overflow: hidden;
        }

        .car-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(52, 152, 219, 0.1), transparent);
            border-radius: var(--border-radius);
            pointer-events: none;
        }

        .summary-row {
            position: relative;
            overflow: hidden;
        }

        .summary-row span {
            position: relative;
            z-index: 1;
        }

        .total-row::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            background: linear-gradient(to right, var(--secondary-color), transparent);
        }

        /* Add these mobile menu styles after the existing navbar styles */
        @media (max-width: 768px) {
            .navbar-container {
                padding: 0.5rem 1rem;
            }

            .nav-menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                max-width: 300px;
                height: 100vh;
                background: var(--primary-color);
                padding: 4rem 1.5rem;
                transition: 0.4s;
                z-index: 1000;
            }

            .nav-menu.active {
                right: 0;
            }

            .nav-list {
                flex-direction: column;
                gap: 2rem;
            }

            .menu-toggle {
                display: flex;
                z-index: 1001;
            }

            .menu-toggle.active span:nth-child(1) {
                transform: rotate(45deg) translate(6px, 6px);
            }

            .menu-toggle.active span:nth-child(2) {
                opacity: 0;
            }

            .menu-toggle.active span:nth-child(3) {
                transform: rotate(-45deg) translate(6px, -6px);
            }

            body.menu-open {
                overflow: hidden;
            }

            body.menu-open::after {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .profile-name {
                display: none;
            }

            .navbar-left {
                flex: 1;
            }
        }
        
        /* Add dark gradient overlay at the top */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 300px;
            background: linear-gradient(to bottom, 
                rgba(0, 0, 0, 0.4) 0%,
                rgba(0, 0, 0, 0.2) 40%,
                rgba(0, 0, 0, 0) 100%);
            pointer-events: none;
            z-index: 900;
        }

        /* Ensure navbar stays above the overlay */
        .navbar {
            position: relative;
            z-index: 1000;
        }

        /* Add these navbar scroll effect styles */
        .navbar.scrolled {
            background: rgba(44, 62, 80, 0.98) !important;
            padding: 0.8rem 2rem;
        }

        .navbar.scrolled .navbar-brand,
        .navbar.scrolled .nav-link {
            color: white !important;
        }

        .navbar.scrolled .profile-toggle {
            color: white !important;
        }

        @media (max-width: 768px) {
            body::before {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="navbar-container">
                <div class="navbar-left">
                    <a href="index.php" class="navbar-brand">
                        <span class="brand-highlight">CARS</span>RENT
                    </a>
                    <div class="nav-menu">
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="index.php" class="nav-link">Home</a>
                            </li>
                            <li class="nav-item">
                                <a href="about.php" class="nav-link">About</a>
                            </li>
                            <li class="nav-item">
                                <a href="book.php" class="nav-link">Cars</a>
                            </li>
                            <li class="nav-item">
                                <a href="#contact" class="nav-link">Contact</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="nav-buttons">
                    <?php if (isset($_SESSION['firstName'])): ?>
                        <div class="profile-dropdown">
                            <button class="profile-toggle">
                                <div class="profile-avatar">
                                    <img src="./images/profile-pic.png" alt="Profile">
                                </div>
                                <span class="profile-name"><?= htmlspecialchars($_SESSION['firstName']); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="profile-menu">
                                <a href="data/homepage.php" class="profile-menu-item">
                                    <i class="fas fa-user"></i> My Account
                                </a>
                                <?php if ($isAdmin): ?>
                                <a href="admin/admin.php" class="profile-menu-item">
                                    <i class="fas fa-cog"></i> Admin Dashboard
                                </a>
                                <?php endif; ?>
                                <a href="data/logout.php" class="profile-menu-item">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="data/index.php" class="nav-btn login-btn">Login</a>
                        <a href="data/index.php" class="nav-btn signup-btn">Sign Up</a>
                    <?php endif; ?>
                </div>

                <button class="menu-toggle" aria-label="Toggle navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </nav>
    </header>

    <div class="container">
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <!-- Main Checkout Form -->
            <div class="checkout-section">
                <h1>Complete Your Booking</h1>
                <p>Please review and confirm your rental details</p>

                <form id="bookingForm" method="POST" action="checkout.php">
                    <input type="hidden" name="car_id" id="car_id" value="<?php echo $car ? $car['id'] : ''; ?>">
                    <input type="hidden" name="submit_booking" value="1">
                    
                    <!-- Add this hidden input after the existing hidden inputs in the form -->
                    <input type="hidden" name="is_preorder" value="<?php echo $is_preorder ? '1' : '0'; ?>">

                    <!-- Step 1: Personal Information -->
                    <div class="progress-step">
                        <div class="step-number active" data-step="1">1</div>
                        <div class="step-content">
                            <h2>Personal Information</h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" class="form-control" value="<?php echo htmlspecialchars($userData['firstName']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" class="form-control" value="<?php echo htmlspecialchars($userData['lastName']); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($userData['phone']); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Rental Dates -->
                    <div class="progress-step">
                        <div class="step-number" data-step="2">2</div>
                        <div class="step-content">
                            <h2>Rental Period</h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="startDate">Pick-up Date & Time</label>
                                    <input type="datetime-local" id="startDate" name="startDate" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="endDate">Return Date & Time</label>
                                    <input type="datetime-local" id="endDate" name="endDate" class="form-control" required>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Minimum rental period is 1 day. Pick-up time must be at least 2 hours from now.
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Payment Information -->
                    <div class="progress-step">
                        <div class="step-number" data-step="3">3</div>
                        <div class="step-content">
                            <h2>Payment Method</h2>
                            <p>Choose your preferred payment method</p>

                            <input type="hidden" id="selected-payment-method" name="payment_method">
                            <input type="hidden" id="payment-data" name="payment_data" value="{}">

                            <div class="payment-methods">
                                <div class="payment-method" data-method="credit-card" onclick="selectPaymentMethod('credit-card')">
                                    <img src="images/visa.png" alt="Credit Card">
                                    <span>Credit Card</span>
                                </div>
                                <div class="payment-method" data-method="paypal" onclick="selectPaymentMethod('paypal')">
                                    <img src="images/paypal.png" alt="PayPal">
                                    <span>PayPal</span>
                                </div>
                                <div class="payment-method" data-method="bank" onclick="selectPaymentMethod('bank')">
                                    <img src="https://cdn-icons-png.flaticon.com/512/2830/2830284.png" alt="Bank Transfer" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2830/2830284.png';">
                                    <span>Bank Transfer</span>
                                </div>
                            </div>

                            <!-- Payment Forms -->
                            <div id="payment-forms">
                                <!-- Credit Card Form -->
                                <div id="credit-card-form" class="payment-form">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card-number">Card Number</label>
                                            <input type="text" id="card-number" class="form-control" placeholder="1234 5678 9012 3456">
                                        </div>
                                        <div class="form-group">
                                            <label for="card-name">Cardholder Name</label>
                                            <input type="text" id="card-name" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card-expiry">Expiry Date</label>
                                            <input type="month" id="card-expiry" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="card-cvv">CVV</label>
                                            <input type="text" id="card-cvv" class="form-control" placeholder="123" maxlength="3">
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="save-card" name="save_payment_info">
                                            <label class="form-check-label" for="save-card">Save card information for future checkouts</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- PayPal Form -->
                                <div id="paypal-form" class="payment-form">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        You will be redirected to PayPal to complete your payment securely.
                                    </div>
                                    <div class="form-group">
                                        <label for="paypal-email">PayPal Email</label>
                                        <input type="email" id="paypal-email" class="form-control" placeholder="your@email.com">
                                    </div>
                                    <div class="form-group mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="save-paypal" name="save_payment_info">
                                            <label class="form-check-label" for="save-paypal">Save PayPal information for future checkouts</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Transfer Form -->
                                <div id="bank-form" class="payment-form">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Please provide your bank account details for direct transfer.
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="bank-account">Account Number</label>
                                            <input type="text" id="bank-account" class="form-control" placeholder="Account Number">
                                        </div>
                                        <div class="form-group">
                                            <label for="bank-routing">Routing Number</label>
                                            <input type="text" id="bank-routing" class="form-control" placeholder="Routing Number">
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="save-bank" name="save_payment_info">
                                            <label class="form-check-label" for="save-bank">Save bank information for future checkouts</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="secure-badge">
                                <i class="fas fa-lock"></i>
                                Your payment information is secure
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem; padding: 1rem;">
                        Complete Booking
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="checkout-section order-summary">
                <h2>Order Summary</h2>
                
                <div class="car-info">
                    <img id="car-image" src="<?php echo $car ? htmlspecialchars($car['image']) : ''; ?>" alt="Selected Car" class="car-image">
                    <div class="car-details">
                        <h3 id="car-name"><?php echo $car ? htmlspecialchars($car['name']) : 'Loading...'; ?></h3>
                        <div class="features-grid">
                            <div class="feature-item">
                                <i class="fas fa-car"></i>
                                <span id="car-brand"><?php echo $car ? htmlspecialchars($car['brand']) : 'Brand'; ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-cog"></i>
                                <span id="car-transmission"><?php echo $car ? htmlspecialchars($car['transmission']) : 'Transmission'; ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-calendar"></i>
                                <span id="car-model"><?php echo $car ? htmlspecialchars($car['model']) : 'Model'; ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-chair"></i>
                                <span id="car-interior"><?php echo $car ? htmlspecialchars($car['interior']) : 'Interior'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add preorder badge and fee if it's a preorder -->
                <?php if ($is_preorder): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i> 
                    This is a pre-order booking. The car will be available from <?php echo date('M d, Y', strtotime($available_from)); ?>
                    <br>
                    <small>*A preorder fee of $15 will be added to your total.</small>
                </div>
                <?php endif; ?>

                <div class="rental-summary">
                    <h3>Price Details</h3>
                    <div class="summary-row">
                        <span>Daily Rate</span>
                        <span id="car-price">$<?php echo $car ? number_format($car['price'], 2) : '0.00'; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Rental Duration</span>
                        <span id="rental-duration">0 days</span>
                    </div>
                    <div class="summary-row">
                        <span>Insurance Fee</span>
                        <span>$25.00</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Amount</span>
                        <span id="total-price">$0.00</span>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-shield-alt"></i>
                    Your booking is protected by our insurance policy
                </div>

                <div class="secure-badge">
                    <i class="fas fa-lock"></i>
                    All transactions are secure and encrypted
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Define the selectPaymentMethod function globally for the onclick handlers
        function selectPaymentMethod(method) {
            // Remove active class from all methods
            document.querySelectorAll('.payment-method').forEach(function(el) {
                el.classList.remove('active');
            });
            
            // Add active class to selected method
            document.querySelector(`.payment-method[data-method="${method}"]`).classList.add('active');
            
            // Hide all payment forms
            document.querySelectorAll('.payment-form').forEach(function(form) {
                form.style.display = 'none';
            });
            
            // Show the selected form
            document.getElementById(`${method}-form`).style.display = 'block';
            
            // Update hidden input
            document.getElementById('selected-payment-method').value = method;
        }
    </script>
    <script src="./js/checkout.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle functionality
            const menuToggle = document.querySelector('.menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            const body = document.body;

            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    menuToggle.classList.toggle('active');
                    navMenu.classList.toggle('active');
                    body.classList.toggle('menu-open');
                });
            }

            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Profile dropdown functionality
            const profileToggle = document.querySelector('.profile-toggle');
            const profileMenu = document.querySelector('.profile-menu');
            
            if (profileToggle) {
                profileToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    profileMenu.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
                        profileMenu.classList.remove('active');
                    }
                });
            }

            // Update payment data before form submission
            document.getElementById('bookingForm').addEventListener('submit', function() {
                const method = document.getElementById('selected-payment-method').value;
                let paymentData = {};
                
                switch(method) {
                    case 'credit-card':
                        paymentData = {
                            cardNumber: document.getElementById('card-number').value,
                            cardName: document.getElementById('card-name').value,
                            cardExpiry: document.getElementById('card-expiry').value,
                            cardCvv: document.getElementById('card-cvv').value
                        };
                        break;
                    case 'paypal':
                        paymentData = {
                            email: document.getElementById('paypal-email').value
                        };
                        break;
                    case 'bank':
                        paymentData = {
                            accountNumber: document.getElementById('bank-account').value,
                            routingNumber: document.getElementById('bank-routing').value
                        };
                        break;
                }
                
                document.getElementById('payment-data').value = JSON.stringify(paymentData);
            });

            // Function to calculate and update price
            function updatePriceCalculation() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const carPrice = <?php echo $car ? $car['price'] : '0'; ?>;
                
                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const duration = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
                    const insuranceFee = 25;
                    const preorderFee = <?php echo $is_preorder ? '50' : '0'; ?>;
                    const totalPrice = (duration * carPrice) + insuranceFee + preorderFee;
                    
                    document.getElementById('rental-duration').textContent = `${duration} days`;
                    document.getElementById('total-price').textContent = `$${totalPrice.toFixed(2)}`;
                }
            }

            // Add event listeners to date inputs for live updates
            document.getElementById('startDate').addEventListener('change', updatePriceCalculation);
            document.getElementById('endDate').addEventListener('change', updatePriceCalculation);
            
            // Set minimum date for startDate to today
            const today = new Date();
            today.setHours(today.getHours() + 2); // Minimum 2 hours from now
            const todayStr = today.toISOString().slice(0, 16);
            document.getElementById('startDate').setAttribute('min', todayStr);

            // Update endDate minimum when startDate changes
            document.getElementById('startDate').addEventListener('change', function() {
                const startDate = new Date(this.value);
                startDate.setDate(startDate.getDate() + 1); // Minimum 1 day rental
                const minEndDate = startDate.toISOString().slice(0, 16);
                document.getElementById('endDate').setAttribute('min', minEndDate);
            });

            // Initial call to update price if dates are pre-filled
            updatePriceCalculation();
        });
    </script>
</body>
</html>