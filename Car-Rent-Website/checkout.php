<?php
session_start();
if (!isset($_SESSION['firstName'])) {
    header('Location: data/authentication.php?action=login');
    exit;
}

// Language selection handling
$availableLangs = ['en', 'fr', 'ar'];
$lang_code = isset($_SESSION['lang']) && in_array($_SESSION['lang'], $availableLangs) ? $_SESSION['lang'] : 'en';

// Set html direction for Arabic
$dir = $lang_code === 'ar' ? 'rtl' : 'ltr';

// Include the selected language file
include_once "languages/{$lang_code}.php";

// Currency conversion rates
$currency_symbols = [
    'en' => '$',
    'fr' => '€',
    'ar' => 'MAD'
];

$currency_rates = [
    'en' => 1,       // USD (base currency)
    'fr' => 0.9,     // EUR (1 USD = 0.9 EUR)
    'ar' => 10       // MAD (1 USD = 10 MAD)
];

// Get currency symbol and rate for the current language
$currency_symbol = $currency_symbols[$lang_code];
$currency_rate = $currency_rates[$lang_code];

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

// Get car details with discount information and images
if (isset($_GET['car_id'])) {
    $stmt = $conn->prepare("
        SELECT c.*, 
            CASE 
                WHEN d.discount_type = 'percentage' THEN c.price * (1 - d.discount_value/100)
                WHEN d.discount_type = 'fixed' THEN c.price - d.discount_value
                ELSE c.price
            END as discounted_price,
            d.discount_type,
            d.discount_value
        FROM cars c
        LEFT JOIN car_discounts d ON c.id = d.car_id 
            AND CURRENT_DATE BETWEEN d.start_date AND d.end_date
        WHERE c.id = ?");
    
    $stmt->bind_param("i", $_GET['car_id']);
    $stmt->execute();
    $car = $stmt->get_result()->fetch_assoc();
    
    // Get car images
    $stmt = $conn->prepare("
        SELECT image_path, is_primary 
        FROM car_images 
        WHERE car_id = ? 
        ORDER BY is_primary DESC, id ASC");
    $stmt->bind_param("i", $_GET['car_id']);
    $stmt->execute();
    $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // If no images in car_images table, use the legacy image field
    if (empty($images) && !empty($car['image'])) {
        $images = [['image_path' => $car['image'], 'is_primary' => 1]];
    }
    
    // If no images at all, use a placeholder
    if (empty($images)) {
        $images = [['image_path' => 'images/placeholder.png', 'is_primary' => 1]];
    }
    
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
        // For preorders, just get the car details with discount info
        $stmt = $conn->prepare("
            SELECT c.*, 
                CASE 
                    WHEN d.discount_type = 'percentage' THEN c.price * (1 - d.discount_value/100)
                    WHEN d.discount_type = 'fixed' THEN c.price - d.discount_value
                    ELSE c.price
                END as discounted_price,
                d.discount_type,
                d.discount_value
            FROM cars c
            LEFT JOIN car_discounts d ON c.id = d.car_id 
                AND CURRENT_DATE BETWEEN d.start_date AND d.end_date
            WHERE c.id = ?");
        $stmt->bind_param("i", $car_id);
    } else {
        // For regular bookings, check availability AND include discount info
        $stmt = $conn->prepare("
            SELECT c.*, 
                CASE 
                    WHEN d.discount_type = 'percentage' THEN c.price * (1 - d.discount_value/100)
                    WHEN d.discount_type = 'fixed' THEN c.price - d.discount_value
                    ELSE c.price
                END as discounted_price,
                d.discount_type,
                d.discount_value,
                (
                    SELECT COUNT(*) 
                    FROM services s 
                    WHERE s.car_id = c.id 
                    AND (
                        CURRENT_DATE BETWEEN DATE(s.start_date) AND DATE(s.end_date)
                        OR DATE(s.start_date) >= CURRENT_DATE
                    )
                ) as active_rentals
            FROM cars c
            LEFT JOIN car_discounts d ON c.id = d.car_id 
                AND CURRENT_DATE BETWEEN d.start_date AND d.end_date
            WHERE c.id = ?");
    }
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $car = $result->fetch_assoc();
        if (!$is_preorder && isset($car['active_rentals']) && $car['active_rentals'] >= $car['quantity']) {
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
                echo json_encode(['success' => true, 'message' => 'Booking successful', 'redirect' => 'confirmation2.php']);
                exit;
            } else {
                // For traditional form submission, redirect to confirmation page
                header("Location: confirmation2.php");
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
<html lang="en" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - CARSRENT</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="./images/image.png">
    <link rel="stylesheet" href="./css/modern.css">
    <link rel="stylesheet" href="./css/checkout.css">
    <link rel="stylesheet" href="./css/language-selector.css">
    <link rel="stylesheet" href="./css/dark-mode.css">
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
            background: #2c3e50; /* Changed from white to a darker color */
            transition: var(--transition);
        }

        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
            background: #2c3e50; /* Ensure visible color when active */
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
            background: #2c3e50; /* Ensure visible color when active */
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
            /* max-width: 1300px; */
            margin: 6rem auto 2rem; /* Increased top margin to account for navbar */
            /* padding: 0 2rem; */
            width: 100%; /* Ensure full width */
        }

        .checkout-section {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 100%; /* Ensure full width */
        }

        .container {
            max-width: 1400px; /* Slightly larger to accommodate the grid */
            margin: 0 auto;
            padding: 0;
            width: 100%;
        }

        @media (max-width: 992px) {
            .checkout-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 0 1rem; /* Smaller padding on mobile */
            }

            .order-summary {
                position: static;
                margin-top: 2rem;
            }
        }

        @media (max-width: 768px) {
            .checkout-grid {
                margin-top: 5rem; /* Slightly less top margin on mobile */
            }
        }

        /* Ensure the car gallery images are properly sized */
        .car-gallery {
            width: 100%;
            margin-bottom: 2rem;
        }

        .main-image-container {
            width: 100%;
            aspect-ratio: 16/9;
            overflow: hidden;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }

        .main-car-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-gallery {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            width: 100%;
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
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .payment-method {
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            min-width: 120px;
            max-width: 100%;
        }

        .payment-method img {
            height: 30px;
            width: auto;
            object-fit: contain;
        }

        /* Responsive styles for payment methods */
        @media (max-width: 768px) {
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .payment-methods {
                grid-template-columns: 1fr;
                max-width: 250px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .payment-method {
                padding: 0.75rem;
            }
            
            .payment-method img {
                height: 25px;
            }
            
            .payment-method span {
                font-size: 0.9rem;
            }
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
            align-items: center;
            padding: 0.75rem 0;
            color: var(--text-color);
        }

        .summary-row span {
            display: flex;
            align-items: center;
            gap: 2px;
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
            border-radius: var (--border-radius);
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
                grid-template-columns: repeat(3, 1fr);
                gap: 0.75rem;
            }
            
            .payment-method {
                padding: 0.75rem;
                min-width: 100px;
            }
            
            .payment-method img {
                height: 25px;
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

        @media (max-width: 480px) {
            .payment-methods {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
            }
            
            .payment-method {
                padding: 0.5rem;
                min-width: 80px;
            }
            
            .payment-method img {
                height: 20px;
            }
            
            .payment-method span {
                font-size: 0.875rem;
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

        /* Add discount styling */
        .price-with-discount {
            display: flex;
            flex-direction: column;
            font-variant-numeric: tabular-nums;
        }

        .discounted-price {
            color: #e74c3c;
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .original-price {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
        }

        .original-price s {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .discount-badge {
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            animation: pulse 2s infinite;
        }

        .discount-row {
            color: #2ecc71;
            font-weight: 500;
        }

        .total-savings {
            color: #2ecc71;
            font-weight: 600;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
            font-variant-numeric: tabular-nums;
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

        .coupon-section .btn-primary {
            min-width: 100px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }

        .coupon-section .form-control {
            flex: 1;
            min-width: 0;
        }
        
        .coupon-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border: 2px dashed #e2e8f0;
        }

        .coupon-section .form-group {
            margin-bottom: 0;
        }

        .coupon-section label {
            color: var(--text-color);
            font-weight: 500;
        }

        .coupon-section .form-control {
            border-right: none;
            height: 45px;
        }

        .coupon-section .btn-primary {
            border: none;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            transition: var(--transition);
        }

        .coupon-section .btn-primary:hover {
            background: var(--primary-color);
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            margin: 0;
            border: none;
            border-radius: var(--border-radius);
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .alert i {
            font-size: 1.1rem;
        }

        /* Add these styles to fix the alignment */
        .coupon-section .input-group {
            display: flex;
            align-items: stretch;
        }

        .coupon-section .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            height: 45px;
            line-height: 45px;
            padding: 0 1rem;
        }

        .coupon-section .btn-primary {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            height: 45px;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: -1px;
        }

        .coupon-section .input-group:focus-within {
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            border-radius: 0.375rem;
        }

        .coupon-section .form-control:focus {
            border-right: none;
            box-shadow: none;
        }

        .coupon-section .btn-primary:focus {
            box-shadow: none;
        }

        /* Update coupon section styles */
        .coupon-section .d-flex {
            display: flex;
            align-items: stretch;
        }

        .coupon-section .form-control {
            flex: 1;
            height: 45px;
            line-height: 45px;
            padding: 0 1rem;
        }

        .coupon-section .btn-primary {
            height: 45px;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .coupon-section .form-control:focus {
            position: relative;
            z-index: 2;
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
                                <a href="index.php" class="nav-link"><?php echo $lang['home']; ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="about.php" class="nav-link"><?php echo $lang['about']; ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="book.php" class="nav-link"><?php echo $lang['cars']; ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#contact" class="nav-link"><?php echo $lang['contact']; ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            <!-- Language Selector -->
            <div class="language-selector">
              <div class="current-lang">
                <span>
                  <?php if($lang_code == 'en'): ?>
                    <i class="flag-icon fas fa-flag flag-icon-uk"></i> EN
                  <?php elseif($lang_code == 'fr'): ?>
                    <i class="flag-icon fas fa-flag flag-icon-france"></i> FR
                  <?php elseif($lang_code == 'ar'): ?>
                    <i class="flag-icon fas fa-flag flag-icon-morocco"></i> AR
                  <?php endif; ?>
                </span>
                <i class="fas fa-chevron-down"></i>
              </div>
              <div class="language-dropdown">
                <a href="data/change-language.php?lang=en&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option">
                    <i class="flag-icon fas fa-flag flag-icon-uk"></i> English
                </a>
                <a href="data/change-language.php?lang=fr&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option">
                    <i class="flag-icon fas fa-flag flag-icon-france"></i> Français
                </a>
                <a href="data/change-language.php?lang=ar&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option">
                    <i class="flag-icon fas fa-flag flag-icon-morocco"></i> العربية
                </a>
              </div>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="theme-switch-wrapper">
                <label class="theme-switch" for="theme-toggle">
                    <input type="checkbox" id="theme-toggle">
                    <span class="slider round">
                        <i class="fas fa-sun"></i>
                        <!-- <i class="fas fa-moon"></i> -->
                    </span>
                </label>
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
                                <a href="data/my_account.php" class="profile-menu-item">
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
                        <div class="auth-buttons">
                            <a href="data/authentication.php?action=login" class="nav-btn login-btn">Login</a>
                            <a href="data/authentication.php?action=register" class="nav-btn signup-btn">Sign Up</a>
                        </div>
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
                <h1><?php echo $lang['complete_booking'] ?? 'Complete Your Booking'; ?></h1>
                <p><?php echo $lang['review_booking'] ?? 'Please review and confirm your rental details'; ?></p>

                <form id="bookingForm" method="POST" action="checkout.php">
                    <input type="hidden" name="car_id" id="car_id" value="<?php echo $car ? $car['id'] : ''; ?>">
                    <input type="hidden" name="submit_booking" value="1">
                    
                    <!-- Add this hidden input after the existing hidden inputs in the form -->
                    <input type="hidden" name="is_preorder" value="<?php echo $is_preorder ? '1' : '0'; ?>">

                    <!-- Step 1: Personal Information -->
                    <div class="progress-step">
                        <div class="step-number active" data-step="1">1</div>
                        <div class="step-content">
                            <h2><?php echo $lang['personal_info'] ?? 'Personal Information'; ?></h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName"><?php echo $lang['first_name'] ?? 'First Name'; ?></label>
                                    <input type="text" id="firstName" name="firstName" class="form-control" value="<?php echo htmlspecialchars($userData['firstName']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName"><?php echo $lang['last_name'] ?? 'Last Name'; ?></label>
                                    <input type="text" id="lastName" name="lastName" class="form-control" value="<?php echo htmlspecialchars($userData['lastName']); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email"><?php echo $lang['email'] ?? 'Email Address'; ?></label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone"><?php echo $lang['phone'] ?? 'Phone Number'; ?></label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($userData['phone']); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Rental Dates -->
                    <div class="progress-step">
                        <div class="step-number" data-step="2">2</div>
                        <div class="step-content">
                            <h2><?php echo $lang['rental_period'] ?? 'Rental Period'; ?></h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="startDate"><?php echo $lang['pickup_date'] ?? 'Pick-up Date & Time'; ?></label>
                                    <input type="datetime-local" id="startDate" name="startDate" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="endDate"><?php echo $lang['return_date'] ?? 'Return Date & Time'; ?></label>
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
                            <h2><?php echo $lang['payment_method'] ?? 'Payment Method'; ?></h2>
                            <p><?php echo $lang['choose_payment'] ?? 'Choose your preferred payment method'; ?></p>

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
                                        <input type="text" id="card-number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" data-required>
                                    </div>
                                    <div class="form-group">
                                        <label for="card-name">Cardholder Name</label>
                                        <input type="text" id="card-name" name="card_name" class="form-control" data-required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="card-expiry">Expiry Date</label>
                                        <input type="month" id="card-expiry" name="card_expiry" class="form-control" data-required>
                                    </div>
                                    <div class="form-group">
                                        <label for="card-cvv">CVV</label>
                                        <input type="text" id="card-cvv" name="card_cvv" class="form-control" placeholder="123" maxlength="3" data-required>
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
                                    <input type="email" id="paypal-email" name="paypal_email" class="form-control" placeholder="your@email.com" data-required>
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
                                        <input type="text" id="bank-account" name="bank_account" class="form-control" placeholder="Account Number" data-required>
                                    </div>
                                    <div class="form-group">
                                        <label for="bank-routing">Routing Number</label>
                                        <input type="text" id="bank-routing" name="bank_routing" class="form-control" placeholder="Routing Number" data-required>
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
                
                <!-- Improved Car Gallery Section (Without Zoom) -->
                <div class="car-gallery">
                    <div class="main-image-container">
                        <img id="main-car-image" src="<?php echo $car ? htmlspecialchars($images[0]['image_path']) : ''; ?>" alt="Selected Car" class="main-car-image">
                    </div>
                    
                    <div class="thumbnail-gallery">
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $index => $image): ?>
                                <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" data-image="<?php echo htmlspecialchars($image['image_path']); ?>">
                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Car image <?php echo $index + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="car-details-expanded">
                    <h3 id="car-name"><?php echo $car ? htmlspecialchars($car['name']) : 'Loading...'; ?></h3>
                    <div class="car-price-tag">
                        <?php if (isset($car['discount_type'])): ?>
                            <div class="price-with-discount">
                                <span class="discounted-price"><?php echo $currency_symbol . number_format($car['discounted_price'] * $currency_rate, 2); ?></span>
                                <div class="original-price">
                                    <span class="text-muted"><s><?php echo $currency_symbol . number_format($car['price'] * $currency_rate, 2); ?></s></span>
                                    <span class="discount-badge" 
                                          data-type="<?php echo htmlspecialchars($car['discount_type']); ?>"
                                          data-value="<?php echo htmlspecialchars($car['discount_value']); ?>">
                                        <?php echo $car['discount_type'] === 'percentage' ? 
                                            number_format($car['discount_value'], 0) . '% OFF' : 
                                            $currency_symbol . number_format($car['discount_value'] * $currency_rate, 2) . ' OFF'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <span id="car-price"><?php echo $currency_symbol . ($car ? number_format($car['price'] * $currency_rate, 2) : '0.00'); ?></span> per day
                        <?php endif; ?>
                    </div>
                    
                    <div class="car-specs">
                        <div class="spec-item">
                            <i class="fas fa-car"></i>
                            <span><strong><?php echo $lang['brand']; ?>:</strong> <span id="car-brand"><?php echo $car ? htmlspecialchars($car['brand']) : 'Brand'; ?></span></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-cog"></i>
                            <span><strong><?php echo $lang['transmission']; ?>:</strong> <span id="car-transmission"><?php echo $car ? $lang['transmission_' . strtolower(str_replace('Automatic', 'auto', $car['transmission']))] : 'Transmission'; ?></span></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-calendar"></i>
                            <span><strong><?php echo $lang['model']; ?>:</strong> <span id="car-model"><?php echo $car ? htmlspecialchars($car['model']) : 'Model'; ?></span></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-chair"></i>
                            <span><strong><?php echo $lang['interior']; ?>:</strong> <span id="car-interior"><?php echo $car ? $lang['interior_' . strtolower($car['interior'])] : 'Interior'; ?></span></span>
                        </div>
                        <?php if (!empty($car['fuel_type'])): ?>
                        <div class="spec-item">
                            <i class="fas fa-gas-pump"></i>
                            <span><strong><?php echo $lang['fuel_type']; ?>:</strong> <span><?php echo $lang['fuel_' . strtolower($car['fuel_type'])]; ?></span></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($car['seating_capacity'])): ?>
                        <div class="spec-item">
                            <i class="fas fa-users"></i>
                            <span><strong><?php echo $lang['seating_capacity']; ?>:</strong> <span><?php echo htmlspecialchars($car['seating_capacity']); ?></span></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($car['features'])): ?>
                    <div class="car-features">
                        <h4>Features</h4>
                        <div class="features-list">
                            <?php 
                            $features = explode(',', $car['features']);
                            foreach ($features as $feature): 
                                $feature = trim($feature);
                                if (!empty($feature)):
                                    // Handle feature content display with or without brackets
                                    $featureText = $feature;
                                    if (substr($featureText, 0, 1) !== '[') {
                                        $featureText = '[' . $featureText;
                                    }
                                    if (substr($featureText, -1) !== ']') {
                                        $featureText = $featureText . ']';
                                    }
                            ?>
                                <div class="feature-badge">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($featureText); ?>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        <div class="features-list">
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($is_preorder): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i> 
                    This is a pre-order booking. The car will be available from <?php echo date('M d, Y', strtotime($available_from)); ?>
                    <br>
                    <small>*A preorder fee of $15 will be added to your total.</small>
                </div>
                <?php endif; ?>

                <div class="rental-summary">
                    <div class="summary-row">
                        <span>Daily Rate</span>
                        <span id="car-price"><?php echo $currency_symbol . ($car ? number_format($car['price'] * $currency_rate, 2) : '0.00'); ?></span>
                    </div>
                    <?php if (isset($car['discount_type'])): ?>
                    <div class="summary-row discount-row">
                        <span>
                            <i class="fas fa-tag text-success me-1"></i>
                            Car Discount
                        </span>
                        <span class="text-success" id="discount-amount"></span>
                    </div>
                    <?php endif; ?>

                    <!-- Improved coupon input section -->
                    <div class="coupon-section mt-4">
                        <div class="form-group">
                            <label for="coupon-code" class="mb-2">Have a coupon code?</label>
                            <div class="input-group">
                                <input type="text" id="coupon-code" class="form-control" placeholder="Enter coupon code" style="border-top-right-radius: 0; border-bottom-right-radius: 0; line-height: 43px;">
                                <button type="button" id="apply-coupon" class="btn btn-primary" style="border-top-left-radius: 0; border-bottom-left-radius: 0; line-height: 43px;">Apply</button>
                            </div>
                        </div>
                        
                        <!-- Coupon alerts -->
                        <div class="alert alert-success mt-2" id="coupon-success" style="display: none;">
                            <i class="fas fa-check-circle"></i> <span></span>
                        </div>
                        <div class="alert alert-danger mt-2" id="coupon-error" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i> <span></span>
                        </div>
                    </div>

                    <div class="summary-row" id="coupon-row" style="display: none;">
                        <span>
                            <i class="fas fa-ticket-alt text-success me-1"></i>
                            Coupon Discount
                        </span>
                        <span class="text-success" id="coupon-discount" data-type="" data-value="0">-$0.00</span>
                    </div>

                    <div class="summary-row">
                        <span>Rental Duration</span>
                        <span id="rental-duration">0 days</span>
                    </div>

                    <div class="summary-row">
                        <span>Insurance Fee</span>
                        <span><?php echo $currency_symbol . '25.00'; ?></span>
                    </div>

                    <?php if ($is_preorder): ?>
                    <div class="summary-row preorder-fee">
                        <span>Pre-order Fee</span>
                        <span><?php echo $currency_symbol . '15.00'; ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="summary-row">
                        <span>Original Price</span>
                        <span id="original-price"><?php echo $currency_symbol . '0.00'; ?></span>
                    </div>

                    <div class="summary-row total-savings">
                        <span>Total Savings</span>
                        <span id="total-savings">-<?php echo $currency_symbol . '0.00'; ?></span>
                    </div>

                    <div class="summary-row total-row">
                        <span>Total Amount</span>
                        <span id="total-price"><?php echo $currency_symbol . '0.00'; ?></span>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="fas fa-shield-alt me-2"></i>
                    Your booking is protected by our insurance policy
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
            
            // Hide all payment forms and disable required fields
            document.querySelectorAll('.payment-form').forEach(function(form) {
                form.style.display = 'none';
                // Remove required attribute from all fields in this form
                form.querySelectorAll('[data-required]').forEach(input => {
                    input.removeAttribute('required');
                });
            });
            
            // Show the selected form and enable its required fields
            const selectedForm = document.getElementById(`${method}-form`);
            selectedForm.style.display = 'block';
            // Add required attribute to fields in the selected form
            selectedForm.querySelectorAll('[data-required]').forEach(input => {
                input.setAttribute('required', 'required');
            });
            
            // Update hidden input
            document.getElementById('selected-payment-method').value = method;
        }
        
        // Call selectPaymentMethod with default payment method (if needed)
        document.addEventListener('DOMContentLoaded', function() {
            // Set credit-card as default payment method
            selectPaymentMethod('credit-card');
            // ...rest of your DOMContentLoaded code...
        });
    </script>
    <!-- Replaced missing checkout.js with inline script -->
    <script>
        // Image Gallery Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get the main image and all thumbnails
            const mainImage = document.getElementById('main-car-image');
            const thumbnails = document.querySelectorAll('.thumbnail-item');
            
            // Add click event to each thumbnail
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    // Get the image source from data attribute
                    const imgSrc = this.getAttribute('data-image');
                    
                    // Update main image source
                    if (mainImage && imgSrc) {
                        mainImage.src = imgSrc;
                        
                        // Remove active class from all thumbnails
                        thumbnails.forEach(t => t.classList.remove('active'));
                        
                        // Add active class to clicked thumbnail
                        this.classList.add('active');
                    }
                });
            });
        });
    </script>
    
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

            // Language selector dropdown functionality
            const languageSelector = document.querySelector('.language-selector');
            const currentLang = document.querySelector('.current-lang');
            
            if (currentLang) {
                currentLang.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    languageSelector.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!languageSelector.contains(e.target)) {
                        languageSelector.classList.remove('active');
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
                const startDate = new Date(document.getElementById('startDate').value);
                const endDate = new Date(document.getElementById('endDate').value);
                
                if (startDate && endDate && !isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                    // Calculate duration in days (always round up to next day)
                    const duration = Math.max(1, Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)));
                    
                    // Update duration display
                    document.getElementById('rental-duration').textContent = `${duration} day${duration > 1 ? 's' : ''}`;
                    
                    const carPrice = <?php echo $car ? $car['price'] : '0'; ?>;
                    const discountedPrice = <?php echo isset($car['discounted_price']) ? $car['discounted_price'] : 'carPrice'; ?>;
                    const insuranceFee = 25;
                    const preorderFee = <?php echo $is_preorder ? '15' : '0'; ?>;
                    
                    // Calculate base amounts
                    const originalTotal = duration * carPrice;
                    const discountedTotal = duration * discountedPrice;
                    let totalPrice = discountedTotal + insuranceFee + preorderFee;
                    
                    // Apply any coupon discount
                    if (couponType === 'percentage') {
                        const couponAmount = totalPrice * (couponValue / 100);
                        totalPrice -= couponAmount;
                        document.getElementById('coupon-discount').textContent = `-<?php echo $currency_symbol; ?>${couponAmount.toFixed(2)}`;
                        document.getElementById('coupon-row').style.display = 'flex';
                    } else if (couponType === 'fixed') {
                        totalPrice -= couponValue;
                        document.getElementById('coupon-discount').textContent = `-<?php echo $currency_symbol; ?>${couponValue.toFixed(2)}`;
                        document.getElementById('coupon-row').style.display = 'flex';
                    }
                    
                    // Update all price displays
                    document.getElementById('original-price').textContent = `<?php echo $currency_symbol; ?>${originalTotal.toFixed(2)}`;
                    document.getElementById('total-price').textContent = `<?php echo $currency_symbol; ?>${totalPrice.toFixed(2)}`;
                    
                    // Calculate and display total savings
                    const totalSavings = originalTotal - totalPrice;
                    document.getElementById('total-savings').textContent = 
                        `-${formatCurrency(Math.max(0, totalSavings)).replace(/[()]/g, '')}`;
                    
                    // Show car discount if applicable
                    if (carPrice !== discountedPrice && document.getElementById('discount-amount')) {
                        const dailyDiscount = carPrice - discountedPrice;
                        document.getElementById('discount-amount').textContent = `-<?php echo $currency_symbol; ?>${dailyDiscount.toFixed(2)}/day`;
                    }
                }
            }

            // Add event listeners for date changes
            document.getElementById('startDate').addEventListener('change', function() {
                const startDate = new Date(this.value);
                startDate.setDate(startDate.getDate() + 1); // Ensure minimum 1 day rental
                const minEndDate = startDate.toISOString().slice(0, 16);
                document.getElementById('endDate').min = minEndDate;
                if (document.getElementById('endDate').value) {
                    const endDate = new Date(document.getElementById('endDate').value);
                    if (endDate <= startDate) {
                        document.getElementById('endDate').value = minEndDate;
                    }
                }
                updatePriceCalculation();
            });
            
            document.getElementById('endDate').addEventListener('change', updatePriceCalculation);
        });

        // Global variables for price calculations
        let basePrice = <?php echo $car ? $car['price'] : 0; ?>;
        let discountedPrice = <?php echo isset($car['discounted_price']) ? $car['discounted_price'] : 'basePrice'; ?>;
        let couponDiscount = 0;
        let couponType = '';
        let couponValue = 0;
        
        // Function to format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }

        // Function to calculate total with all discounts
        function calculateTotal() {
            const durationDays = parseInt(document.getElementById('rental-duration').textContent) || 0;
            const baseTotal = durationDays * basePrice;
            const insuranceFee = 25;
            const preorderFee = <?php echo $is_preorder ? '15' : '0'; ?>;
            
            let total = durationDays * discountedPrice + insuranceFee + preorderFee;
            const originalTotal = baseTotal + insuranceFee + preorderFee;
            
            // Apply coupon discount if exists
            if (couponType === 'percentage') {
                let couponAmount = total * (couponValue / 100);
                total -= couponAmount;
                couponDiscount = couponAmount;
            } else if (couponType === 'fixed') {
                total -= couponValue;
                couponDiscount = couponValue;
            }
            
            // Update displays
            document.getElementById('original-price').textContent = formatCurrency(originalTotal);
            document.getElementById('total-price').textContent = formatCurrency(total);
            
            // Calculate and show total savings
            const totalSavings = originalTotal - total;
            document.getElementById('total-savings').textContent = 
                `-${formatCurrency(Math.max(0, totalSavings)).replace(/[()]/g, '')}`;
            
            // Update coupon discount display if applicable
            const couponRow = document.getElementById('coupon-row');
            if (couponDiscount > 0) {
                couponRow.style.display = 'flex';
                document.getElementById('coupon-discount').textContent = 
                    `-<?php echo $currency_symbol; ?>${couponDiscount.toFixed(2)}`;
            } else {
                couponRow.style.display = 'none';
            }
        }

        // Coupon validation and application
        document.getElementById('apply-coupon').addEventListener('click', function() {
            const couponCode = document.getElementById('coupon-code').value;
            
            // Reset previous alerts
            document.getElementById('coupon-success').style.display = 'none';
            document.getElementById('coupon-error').style.display = 'none';
            
            if (!couponCode) {
                document.getElementById('coupon-error').querySelector('span').textContent = 'Please enter a coupon code';
                document.getElementById('coupon-error').style.display = 'block';
                return;
            }
            
            // Validate coupon via AJAX
            $.ajax({
                url: 'data/validate_coupon.php',
                method: 'POST',
                data: {
                    code: couponCode,
                    car_id: <?php echo $car ? $car['id'] : '0'; ?>
                },
                success: function(response) {
                    if (response.valid) {
                        couponType = response.type;
                        couponValue = parseFloat(response.value);
                        
                        // Show success message
                        const successMsg = response.type === 'percentage' 
                            ? `${response.value}% discount applied!` 
                            : `$${response.value} discount applied!`;
                        document.getElementById('coupon-success').querySelector('span').textContent = successMsg;
                        document.getElementById('coupon-success').style.display = 'block';
                        
                        // Update prices
                        calculateTotal();
                    } else {
                        couponType = '';
                        couponValue = 0;
                        document.getElementById('coupon-error').querySelector('span').textContent = response.message || 'Invalid coupon code';
                        document.getElementById('coupon-error').style.display = 'block';
                        calculateTotal();
                    }
                },
                error: function() {
                    document.getElementById('coupon-error').querySelector('span').textContent = 'Error validating coupon';
                    document.getElementById('coupon-error').style.display = 'block';
                }
            });
        });

        // Add styles for coupon section
        const style = document.createElement('style');
        style.textContent = `
            .coupon-section {
                background: #f8fafc;
                padding: 1.5rem;
                border-radius: 1rem;
                border: 2px dashed #e2e8f0;
            }

            .coupon-section .form-group {
                margin-bottom: 0;
            }

            .coupon-section label {
                color: var(--text-color);
                font-weight: 500;
            }

            .coupon-section .form-control {
                border-right: none;
                height: 45px;
            }

            .coupon-section .btn-primary {
                border: none;
                height: 45px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 500;
                transition: var(--transition);
            }

            .coupon-section .btn-primary:hover {
                background: var(--primary-color);
            }

            /* Ensure clean connection between input and button */
            .coupon-section .form-control:focus {
                border-right: none;
                box-shadow: none;
            }

            .coupon-section .btn-primary {
                margin-left: -1px;
            }

            .alert {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1rem;
                margin: 0;
                border: none;
                border-radius: var(--border-radius);
            }

            .alert-success {
                background: rgba(46, 204, 113, 0.1);
                color: #2ecc71;
            }

            .alert-danger {
                background: rgba(231, 76, 60, 0.1);
                color: #e74c3c;
            }

            .alert i {
                font-size: 1.1rem;
            }
        `;
        document.head.appendChild(style);
    </script>
    <script>
        // Dark mode functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.documentElement;

            // Check for saved theme preference, otherwise use system preference
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Set initial theme
            if (savedTheme) {
                body.setAttribute('data-theme', savedTheme);
                themeToggle.checked = savedTheme === 'dark';
            } else if (prefersDark) {
                body.setAttribute('data-theme', 'dark');
                themeToggle.checked = true;
            }

            // Handle theme toggle
            themeToggle.addEventListener('change', function() {
                const theme = this.checked ? 'dark' : 'light';
                body.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
            });
        });
    </script>
</body>
</html>