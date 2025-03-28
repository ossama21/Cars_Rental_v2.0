<?php
session_start();
if (!isset($_SESSION['firstName'])) {
    header('Location: data/authentication.php?action=login');
    exit;
}

// Include database connection
include 'data/connect.php';

// Language selection handling
$availableLangs = ['en', 'fr', 'ar'];
$lang_code = isset($_SESSION['lang']) && in_array($_SESSION['lang'], $availableLangs) ? $_SESSION['lang'] : 'en';
$dir = $lang_code === 'ar' ? 'rtl' : 'ltr';
include_once "languages/{$lang_code}.php";

// Currency settings
$currency_symbols = [
    'en' => '$',
    'fr' => '€',
    'ar' => 'MAD'
];

$currency_rates = [
    'en' => 1,       // USD (base currency)
    'fr' => 0.9,     // EUR
    'ar' => 10       // MAD
];

$currency_symbol = $currency_symbols[$lang_code];
$currency_rate = $currency_rates[$lang_code];

// Process car_id and preorder parameters
$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;
$is_preorder = isset($_GET['preorder']) && $_GET['preorder'] == 1;
$available_from = isset($_GET['available_from']) ? $_GET['available_from'] : null;

// Get car details with discount information and images
if ($car_id > 0) {
    $stmt = $conn->prepare("
        SELECT c.*, 
            CASE 
                WHEN d.discount_type = 'percentage' THEN c.price * (1 - d.discount_value/100)
                WHEN d.discount_type = 'fixed' THEN c.price - d.discount_value
                ELSE c.price
            END as discounted_price,
            d.discount_type,
            d.discount_value,
            d.end_date as discount_end
        FROM cars c
        LEFT JOIN car_discounts d ON c.id = d.car_id 
            AND CURRENT_DATE BETWEEN d.start_date AND d.end_date
        WHERE c.id = ?");
    
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $car = $stmt->get_result()->fetch_assoc();

    if (!$car) {
        header("Location: book.php?error=car_not_found");
        exit;
    }

    // Get car images (up to 4)
    $stmt = $conn->prepare("
        SELECT image_path, is_primary 
        FROM car_images 
        WHERE car_id = ? 
        ORDER BY is_primary DESC, id ASC 
        LIMIT 4");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // If no images in car_images table, use legacy image
    if (empty($images) && !empty($car['image'])) {
        $images = [['image_path' => $car['image'], 'is_primary' => 1]];
    }

    // Use placeholder if no images available
    if (empty($images)) {
        $images = [['image_path' => 'images/placeholder.png', 'is_primary' => 1]];
    }
} else {
    header("Location: book.php?error=invalid_car");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        $name = $_POST['firstName'] . ' ' . $_POST['lastName'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $start_date = $_POST['startDate'];
        $end_date = $_POST['endDate'];
        $payment_method = $_POST['payment_method'] ?? 'unknown';
        $payment_data = $_POST['payment_data'] ?? '{}';
        
        // Calculate duration and amount
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $duration = max(1, $start->diff($end)->days);
        
        // Calculate total amount with discounts
        $daily_rate = isset($car['discounted_price']) ? $car['discounted_price'] : $car['price'];
        $base_amount = $duration * $daily_rate;
        $insurance_fee = 25;
        $preorder_fee = $is_preorder ? 15 : 0;
        $total_amount = $base_amount + $insurance_fee + $preorder_fee;

        // Insert booking into services table
        $stmt = $conn->prepare("
            INSERT INTO services (
                username, phone, email, start_date, end_date, 
                duration, amount, car_id, payment_method, 
                payment_details, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $status = $is_preorder ? 'preorder' : 'upcoming';
        $stmt->bind_param(
            "sssssidiiss",
            $name, $phone, $email, $start_date, $end_date,
            $duration, $total_amount, $car_id, $payment_method,
            $payment_data, $status
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to save booking: " . $stmt->error);
        }

        $booking_id = $conn->insert_id;

        // Create transaction ID and record payment
        $transaction_id = 'TXN' . time() . rand(1000, 9999);
        $stmt = $conn->prepare("
            INSERT INTO payments (
                booking_id, method, amount, status, transaction_id
            ) VALUES (?, ?, ?, 'completed', ?)
        ");
        
        $stmt->bind_param("isds", $booking_id, $payment_method, $total_amount, $transaction_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to record payment: " . $stmt->error);
        }

        // Save payment method if requested
        if (isset($_POST['save_payment_info']) && $_POST['save_payment_info'] === 'on' && isset($_SESSION['id'])) {
            $user_id = $_SESSION['id'];
            
            // Check if payment method already exists
            $stmt = $conn->prepare("
                SELECT id FROM saved_payment_methods 
                WHERE user_id = ? AND payment_type = ? AND payment_details = ?
            ");
            $stmt->bind_param("iss", $user_id, $payment_method, $payment_data);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows === 0) {
                $stmt = $conn->prepare("
                    INSERT INTO saved_payment_methods (
                        user_id, payment_type, payment_details
                    ) VALUES (?, ?, ?)
                ");
                $stmt->bind_param("iss", $user_id, $payment_method, $payment_data);
                $stmt->execute();
            }
        }

        $conn->commit();

        // Store booking details in session for confirmation page
        $_SESSION['booking'] = [
            'id' => $booking_id,
            'transaction_id' => $transaction_id,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'startDate' => $start_date,
            'endDate' => $end_date,
            'duration' => $duration,
            'amount' => $total_amount,
            'paymentMethod' => $payment_method,
            'car' => $car
        ];

        header("Location: confirmation2.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Get user data
$userData = [
    'firstName' => $_SESSION['firstName'] ?? '',
    'lastName' => $_SESSION['lastName'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'phone' => $_SESSION['phone'] ?? ''
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['checkout'] ?? 'Checkout'; ?> - CARSRENT</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/modern.css">
    <link rel="stylesheet" href="./css/checkout.css">
    <link rel="stylesheet" href="./css/language-selector.css">
    <link rel="stylesheet" href="./css/dark-mode.css">
    <link rel="icon" type="image/png" href="./images/image.png">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --background-color: #f5f7fa;
            --text-color: #2c3e50;
            --border-color: #e2e8f0;
            --input-bg: #ffffff;
            --input-text: #2c3e50;
            --card-bg: #ffffff;
            --border-radius: 12px;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        /* Dark mode variables */
        [data-theme="dark"] {
            --primary-color: #60a5fa;
            --secondary-color: #63b3ed; 
            --accent-color: #f87171;
            --success-color: #34d399;
            --warning-color: #fbbf24;
            --background-color: #111827;
            --text-color: #f3f4f6;
            --border-color: #374151;
            --input-bg: #1f2937;
            --input-text: #f3f4f6;
            --card-bg: #1e293b;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
        }

        /* Dark mode specific styles */
        [data-theme="dark"] body {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        [data-theme="dark"] .checkout-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        [data-theme="dark"] .step {
            background: var(--input-bg);
        }

        [data-theme="dark"] .step.active {
            background: var(--primary-color);
        }

        [data-theme="dark"] .form-control {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .form-control:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--primary-color);
        }

        [data-theme="dark"] .payment-method {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .payment-method.active {
            border-color: var(--primary-color);
            background: rgba(96, 165, 250, 0.15);
        }

        [data-theme="dark"] .payment-method span {
            color: var(--text-color);
        }

        [data-theme="dark"] .price-breakdown {
            background: var(--input-bg);
        }

        [data-theme="dark"] .summary-row {
            color: var(--text-color);
        }

        [data-theme="dark"] .total-row {
            border-color: var(--border-color);
        }

        [data-theme="dark"] .coupon-section {
            background: var(--input-bg);
            border: 2px dashed var(--border-color);
        }

        [data-theme="dark"] .alert-info {
            background-color: rgba(52, 152, 219, 0.15);
            color: var(--text-color);
            border-color: rgba(52, 152, 219, 0.3);
        }

        [data-theme="dark"] .alert-success {
            background-color: rgba(46, 204, 113, 0.15);
            color: var(--text-color);
            border-color: rgba(46, 204, 113, 0.3);
        }

        [data-theme="dark"] .alert-danger {
            background-color: rgba(231, 76, 60, 0.15);
            color: var(--text-color);
            border-color: rgba(231, 76, 60, 0.3);
        }

        [data-theme="dark"] .form-check-label {
            color: var(--text-color);
        }

        [data-theme="dark"] .loading-overlay {
            background: rgba(20, 30, 40, 0.9);
        }

        [data-theme="dark"] .loading-spinner {
            border-color: var(--input-bg);
            border-top-color: var(--primary-color);
        }

        [data-theme="dark"] .thumbnail {
            border-color: var(--border-color);
            background: var(--input-bg);
        }

        [data-theme="dark"] .thumbnail.active {
            border-color: var(--primary-color);
        }

        /* Improved header styling */
        .navbar {
            background: rgb(16 25 44) !important;
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1030;
            transition: all 0.4s ease;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced checkout container */
        .checkout-container {
            max-width: 1400px;
            margin: 6rem auto 2rem;
            padding: 0 2rem;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2.5rem;
            width: 100%;
        }

        .checkout-section {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        /* Improved step progress */
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--background-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-color);
            position: relative;
            z-index: 1;
            border: 2px solid var(--primary-color);
        }

        .step.active {
            background: var(--primary-color);
            color: white;
        }

        .step.completed {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        /* Enhanced form styling */
        .form-control {
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            font-family: inherit;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        /* Improved payment methods */
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
        }

        .payment-method.active {
            border-color: var(--secondary-color);
            background: rgba(52, 152, 219, 0.1);
        }

        .payment-method img {
            height: 30px;
            width: auto;
            object-fit: contain;
        }

        /* Enhanced car gallery */
        .car-gallery {
            margin-bottom: 2rem;
        }

        .main-image-container {
            position: relative;
            width: 100%;
            margin-bottom: 1rem;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .main-image-container img {
            width: 100%;
            height: auto;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-thumbnails {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
        }

        .thumbnail {
            width: 100%;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            opacity: 0.6;
            transition: var(--transition);
        }

        .thumbnail.active {
            opacity: 1;
            border: 2px solid var(--secondary-color);
        }

        /* Improved summary section */
        .price-breakdown {
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

        .total-row {
            border-top: 2px solid var(--primary-color);
            margin-top: 1rem;
            padding-top: 1rem;
            font-weight: 600;
        }

        /* Enhanced coupon section */
        .coupon-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border: 2px dashed #e2e8f0;
            margin-top: 1.5rem;
        }

        .coupon-input {
            display: flex;
            gap: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-overlay.show {
            display: flex;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--background-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
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
                                <a href="data/homepage.php" class="profile-menu-item">
                                    <i class="fas fa-user"></i> My Account
                                </a>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="admin/admin.php" class="profile-menu-item">
                                    <i class="fas fa-cog"></i> Admin Dashboard
                                </a>
                                <?php endif; ?>
                                <a href="data/logout.php" class="profile-menu-item">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
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

    <div class="checkout-container">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <!-- Main Checkout Form -->
            <div class="checkout-section">
                <div class="step-progress">
                    <div class="step active" data-step="1">1</div>
                    <div class="step" data-step="2">2</div>
                    <div class="step" data-step="3">3</div>
                </div>

                <form id="checkoutForm" method="POST">
                    <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                    <input type="hidden" name="is_preorder" value="<?php echo $is_preorder ? '1' : '0'; ?>">

                    <!-- Step 1: Personal Information -->
                    <div class="checkout-step" id="step1">
                        <h3><?php echo $lang['personal_info'] ?? 'Personal Information'; ?></h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label"><?php echo $lang['first_name'] ?? 'First Name'; ?></label>
                                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($userData['firstName']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label"><?php echo $lang['last_name'] ?? 'Last Name'; ?></label>
                                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($userData['lastName']); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label"><?php echo $lang['email'] ?? 'Email'; ?></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label"><?php echo $lang['phone'] ?? 'Phone'; ?></label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Rental Details -->
                    <div class="checkout-step" id="step2" style="display: none;">
                        <h3><?php echo $lang['rental_details'] ?? 'Rental Details'; ?></h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="startDate" class="form-label"><?php echo $lang['pickup_date'] ?? 'Pick-up Date & Time'; ?></label>
                                <input type="datetime-local" class="form-control" id="startDate" name="startDate" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="endDate" class="form-label"><?php echo $lang['return_date'] ?? 'Return Date & Time'; ?></label>
                                <input type="datetime-local" class="form-control" id="endDate" name="endDate" required>
                            </div>
                        </div>
                        
                        <!-- Coupon Section -->
                        <div class="coupon-section">
                            <label for="couponCode" class="form-label"><?php echo $lang['have_coupon'] ?? 'Have a coupon code?'; ?></label>
                            <div class="coupon-input">
                                <input type="text" class="form-control" id="couponCode" name="coupon_code" placeholder="Enter coupon code">
                                <button type="button" class="btn btn-primary" id="applyCoupon"><?php echo $lang['apply'] ?? 'Apply'; ?></button>
                            </div>
                            <div class="coupon-message alert alert-success" style="display: none;"></div>
                            <div class="coupon-message alert alert-danger" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Step 3: Payment -->
                    <div class="checkout-step" id="step3" style="display: none;">
                        <h3><?php echo $lang['payment'] ?? 'Payment'; ?></h3>
                        <input type="hidden" id="selected-payment-method" name="payment_method">
                        <input type="hidden" id="payment-data" name="payment_data" value="{}">

                        <div class="payment-methods">
                            <div class="payment-method" data-method="credit-card">
                                <img src="images/visa.png" alt="Credit Card">
                                <span>Credit Card</span>
                            </div>
                            <div class="payment-method" data-method="paypal">
                                <img src="images/paypal.png" alt="PayPal">
                                <span>PayPal</span>
                            </div>
                            <div class="payment-method" data-method="bank">
                                <img src="images/bank.png" alt="Bank Transfer">
                                <span>Bank Transfer</span>
                            </div>
                        </div>

                        <!-- Payment Forms -->
                        <div id="payment-forms">
                            <!-- Credit Card Form -->
                            <div id="credit-card-form" class="payment-form" style="display: none;">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="card-number" class="form-label">Card Number</label>
                                        <input type="text" class="form-control" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="card-name" class="form-label">Cardholder Name</label>
                                        <input type="text" class="form-control" id="card-name">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="card-expiry" class="form-label">Expiry Date</label>
                                        <input type="month" class="form-control" id="card-expiry">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="card-cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="card-cvv" maxlength="3">
                                    </div>
                                </div>
                            </div>

                            <!-- PayPal Form -->
                            <div id="paypal-form" class="payment-form" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    You will be redirected to PayPal to complete your payment securely.
                                </div>
                                <div class="mb-3">
                                    <label for="paypal-email" class="form-label">PayPal Email</label>
                                    <input type="email" class="form-control" id="paypal-email">
                                </div>
                            </div>

                            <!-- Bank Transfer Form -->
                            <div id="bank-form" class="payment-form" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="bank-account" class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="bank-account">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="bank-routing" class="form-label">Routing Number</label>
                                        <input type="text" class="form-control" id="bank-routing">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="save-payment" name="save_payment_info">
                            <label class="form-check-label" for="save-payment">Save payment method for future rentals</label>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="prevStep" style="display: none;">Back</button>
                        <button type="button" class="btn btn-primary" id="nextStep">Next</button>
                        <button type="submit" class="btn btn-primary" id="submitBooking" style="display: none;">Complete Booking</button>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="checkout-section order-summary">
                <h3>Order Summary</h3>
                
                <!-- Car Gallery -->
                <div class="car-gallery">
                    <?php if (!empty($images)): ?>
                        <div class="main-image-container">
                            <img src="<?php echo htmlspecialchars($images[0]['image_path']); ?>" alt="Car" class="main-image" id="mainCarImage">
                        </div>
                        <?php if (count($images) > 1): ?>
                            <div class="gallery-thumbnails">
                                <?php foreach ($images as $index => $image): ?>
                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         alt="Car view <?php echo $index + 1; ?>"
                                         class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                         onclick="updateMainImage(this)">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Car Details -->
                <div class="car-details mb-4">
                    <h4><?php echo $car ? htmlspecialchars($car['name']) : ''; ?></h4>
                    <div class="specs">
                        <div><i class="fas fa-car"></i> <?php echo $car ? htmlspecialchars($car['brand']) : ''; ?></div>
                        <div><i class="fas fa-cog"></i> <?php echo $car ? htmlspecialchars($car['transmission']) : ''; ?></div>
                        <div><i class="fas fa-user"></i> <?php echo $car ? htmlspecialchars($car['seating_capacity']) : ''; ?> seats</div>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="price-breakdown">
                    <div class="summary-row">
                        <span>Daily Rate</span>
                        <span class="daily-rate">
                            <?php if (isset($car['discount_type'])): ?>
                                <s class="text-muted"><?php echo $currency_symbol . number_format($car['price'] * $currency_rate, 2); ?></s>
                                <span class="text-success"><?php echo $currency_symbol . number_format($car['discounted_price'] * $currency_rate, 2); ?></span>
                            <?php else: ?>
                                <?php echo $currency_symbol . number_format($car['price'] * $currency_rate, 2); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="summary-row">
                        <span>Duration</span>
                        <span id="rentalDuration">0 days</span>
                    </div>
                    <div class="summary-row">
                        <span>Insurance Fee</span>
                        <span><?php echo $currency_symbol; ?>25.00</span>
                    </div>
                    <?php if ($is_preorder): ?>
                    <div class="summary-row">
                        <span>Pre-order Fee</span>
                        <span><?php echo $currency_symbol; ?>15.00</span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row discount-row" style="display: none;">
                        <span>Discount</span>
                        <span class="text-success" id="discountAmount">-<?php echo $currency_symbol; ?>0.00</span>
                    </div>
                    <div class="summary-row coupon-row" style="display: none;">
                        <span>Coupon Discount</span>
                        <span class="text-success" id="couponDiscount">-<?php echo $currency_symbol; ?>0.00</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Amount</span>
                        <span id="totalAmount"><?php echo $currency_symbol; ?>0.00</span>
                    </div>
                </div>

                <?php if ($is_preorder): ?>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        This is a pre-order booking. The car will be available from <?php echo date('M d, Y', strtotime($available_from)); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-success mt-3">
                    <i class="fas fa-shield-alt"></i>
                    Your booking is protected by our insurance policy
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize variables
        let currentStep = 1;
        const totalSteps = 3;
        let selectedPaymentMethod = '';
        
        // Get current date and time
        const now = new Date();
        now.setMinutes(now.getMinutes() + 120); // Add 2 hours minimum
        const minDateTime = now.toISOString().slice(0, 16);
        
        // Set minimum date/time for pickup
        document.getElementById('startDate').setAttribute('min', minDateTime);
        
        // Dark mode toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const htmlElement = document.documentElement;
            
            // Check for saved user preference
            const savedTheme = localStorage.getItem('theme');
            
            // Check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Set initial theme
            if (savedTheme) {
                htmlElement.setAttribute('data-theme', savedTheme);
                themeToggle.checked = savedTheme === 'dark';
            } else if (prefersDark) {
                htmlElement.setAttribute('data-theme', 'dark');
                themeToggle.checked = true;
            }
            
            // Handle theme toggle
            themeToggle.addEventListener('change', function() {
                const theme = this.checked ? 'dark' : 'light';
                htmlElement.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
            });
        });
        
        // Function to update main car image
        function updateMainImage(thumbnail) {
            const mainImage = document.getElementById('mainCarImage');
            mainImage.src = thumbnail.src;
            document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
            thumbnail.classList.add('active');
        }
        
        // Function to show error message
        function showError(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.checkout-container').insertBefore(alert, document.querySelector('.checkout-grid'));
        }
        
        // Function to update price calculations
        function updatePriceCalculations() {
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            
            if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                const duration = Math.max(1, Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)));
                const dailyRate = <?php echo isset($car['discounted_price']) ? $car['discounted_price'] : $car['price']; ?>;
                const insuranceFee = 25;
                const preorderFee = <?php echo $is_preorder ? '15' : '0'; ?>;
                
                let subtotal = duration * dailyRate;
                let totalAmount = subtotal + insuranceFee + preorderFee;
                
                // Update display
                document.getElementById('rentalDuration').textContent = `${duration} day${duration > 1 ? 's' : ''}`;
                document.getElementById('totalAmount').textContent = `<?php echo $currency_symbol; ?>${totalAmount.toFixed(2)}`;
            }
        }
        
        // Event listeners for date inputs
        document.getElementById('startDate').addEventListener('change', function() {
            const startDate = new Date(this.value);
            startDate.setDate(startDate.getDate() + 1);
            document.getElementById('endDate').setAttribute('min', startDate.toISOString().slice(0, 16));
            updatePriceCalculations();
        });
        
        document.getElementById('endDate').addEventListener('change', updatePriceCalculations);
        
        // Handle step navigation
        document.getElementById('nextStep').addEventListener('click', function() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateStepDisplay();
                }
            }
        });
        
        document.getElementById('prevStep').addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateStepDisplay();
            }
        });
        
        // Function to validate current step
        function validateCurrentStep() {
            const currentStepElement = document.getElementById(`step${currentStep}`);
            const requiredFields = currentStepElement.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Add error message if it doesn't exist
                    if (!field.nextElementSibling?.classList.contains('error-feedback')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'error-feedback show';
                        errorDiv.textContent = 'This field is required';
                        field.parentNode.insertBefore(errorDiv, field.nextSibling);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const errorDiv = field.nextElementSibling;
                    if (errorDiv?.classList.contains('error-feedback')) {
                        errorDiv.remove();
                    }
                }
            });
            
            if (!isValid) {
                showError('Please fill in all required fields');
            }
            
            return isValid;
        }
        
        // Function to update step display
        function updateStepDisplay() {
            // Update step indicators
            document.querySelectorAll('.step').forEach((step, index) => {
                if (index + 1 < currentStep) {
                    step.classList.add('completed');
                    step.classList.remove('active');
                } else if (index + 1 === currentStep) {
                    step.classList.add('active');
                    step.classList.remove('completed');
                } else {
                    step.classList.remove('active', 'completed');
                }
            });
            
            // Show/hide step content
            document.querySelectorAll('.checkout-step').forEach((step, index) => {
                step.style.display = index + 1 === currentStep ? 'block' : 'none';
            });
            
            // Update navigation buttons
            document.getElementById('prevStep').style.display = currentStep > 1 ? 'block' : 'none';
            document.getElementById('nextStep').style.display = currentStep < totalSteps ? 'block' : 'none';
            document.getElementById('submitBooking').style.display = currentStep === totalSteps ? 'block' : 'none';
        }
        
        // Handle payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                const methodName = this.dataset.method;
                selectedPaymentMethod = methodName;
                
                // Update UI
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('active'));
                this.classList.add('active');
                
                // Show relevant form
                document.querySelectorAll('.payment-form').forEach(form => form.style.display = 'none');
                document.getElementById(`${methodName}-form`).style.display = 'block';
                
                // Update hidden input
                document.getElementById('selected-payment-method').value = methodName;
            });
        });
        
        // Handle form submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateCurrentStep()) {
                return;
            }
            
            const loadingOverlay = document.querySelector('.loading-overlay');
            loadingOverlay.classList.add('show');
            
            // Collect payment details based on selected method
            let paymentDetails = {};
            switch (selectedPaymentMethod) {
                case 'credit-card':
                    paymentDetails = {
                        cardNumber: document.getElementById('card-number').value,
                        cardName: document.getElementById('card-name').value,
                        cardExpiry: document.getElementById('card-expiry').value,
                        cardCvv: document.getElementById('card-cvv').value
                    };
                    break;
                case 'paypal':
                    paymentDetails = {
                        email: document.getElementById('paypal-email').value
                    };
                    break;
                case 'bank':
                    paymentDetails = {
                        accountNumber: document.getElementById('bank-account').value,
                        routingNumber: document.getElementById('bank-routing').value
                    };
                    break;
            }
            
            document.getElementById('payment-data').value = JSON.stringify(paymentDetails);
            
            // Submit the form
            this.submit();
        });
        
        // Initialize coupon handling
        document.getElementById('applyCoupon').addEventListener('click', function() {
            const couponCode = document.getElementById('couponCode').value.trim();
            if (!couponCode) {
                showError('Please enter a coupon code');
                return;
            }
            
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            
            if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                showError('Please select rental dates before applying a coupon');
                return;
            }
            
            fetch('data/validate_coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    code: couponCode,
                    car_id: <?php echo $car_id; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.coupon-message.alert-success').textContent = data.message;
                    document.querySelector('.coupon-message.alert-success').style.display = 'block';
                    document.querySelector('.coupon-message.alert-danger').style.display = 'none';
                    
                    // Update price display
                    document.querySelector('.coupon-row').style.display = 'flex';
                    updatePriceCalculations();
                } else {
                    document.querySelector('.coupon-message.alert-danger').textContent = data.error;
                    document.querySelector('.coupon-message.alert-danger').style.display = 'block';
                    document.querySelector('.coupon-message.alert-success').style.display = 'none';
                }
            })
            .catch(error => {
                showError('Error validating coupon');
            });
        });
        
        // Handle credit card input formatting
        document.getElementById('card-number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(.{4})/g, '$1 ').trim();
            e.target.value = value;
        });
        
        document.getElementById('card-cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
        });
        
        // Initialize steps display
        updateStepDisplay();
    </script>
</body>
</html>