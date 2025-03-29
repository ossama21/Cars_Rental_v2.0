<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "car_rent";

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$booking = null;
$bookingId = 0;
$bookingConfirmed = false;

// Handle booking information from session
if (isset($_SESSION['booking'])) {
    $booking = $_SESSION['booking'];
    $bookingId = isset($booking['bookingId']) ? $booking['bookingId'] : 0;
    
    if ($bookingId > 0) {
        // Verify booking exists in database
        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookingConfirmed = ($result->num_rows > 0);
        $stmt->close();
    }
} 
// Check if we have a booking ID in URL
elseif (isset($_GET['booking_id'])) {
    $bookingId = intval($_GET['booking_id']);
    
    // Get booking details from database
    $stmt = $conn->prepare("SELECT s.*, c.name as car_name, c.brand, c.model, c.image, c.price, c.transmission, c.interior, 
                          (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image  
                          FROM services s 
                          JOIN cars c ON s.car_id = c.id 
                          WHERE s.id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $bookingData = $result->fetch_assoc();
        $bookingConfirmed = true;
        
        // Format booking data to match session structure
        $booking = [
            'username' => $bookingData['username'],
            'phone' => $bookingData['phone'],
            'email' => $bookingData['email'],
            'startDate' => $bookingData['start_date'],
            'endDate' => $bookingData['end_date'],
            'duration' => $bookingData['duration'],
            'car' => [
                'id' => $bookingData['car_id'],
                'name' => $bookingData['car_name'],
                'brand' => $bookingData['brand'],
                'model' => $bookingData['model'],
                'image' => $bookingData['primary_image'] ? $bookingData['primary_image'] : $bookingData['image'],
                'price' => $bookingData['price'],
                'transmission' => $bookingData['transmission'],
                'interior' => $bookingData['interior'] ?? 'Standard'
            ],
            'paymentMethod' => $bookingData['payment_method'],
            'paymentDetails' => $bookingData['payment_details'],
            'totalAmount' => $bookingData['amount'],
            'bookingId' => $bookingData['id']
        ];
    }
    $stmt->close();
}
// If no booking data available
else {
    $noBookingData = true;
}

// Get payment details
$paymentDetails = [];
if ($bookingId > 0) {
    $stmt = $conn->prepare("SELECT * FROM payments WHERE booking_id = ? ORDER BY date DESC LIMIT 1");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $paymentDetails = $result->fetch_assoc();
    }
    $stmt->close();
}

// Close connection
$conn->close();

// Clean up the session variable only after we've used it
if (isset($_SESSION['booking'])) {
    $tempBooking = $_SESSION['booking'];
    unset($_SESSION['booking']);
    $booking = $tempBooking;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - CARSRENT</title>
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
            --border-radius: 10px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .navbar {
            background: #172e53;
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1030;
            transition: all 0.4s ease;
        }

        /* Light mode styles */
        body {
            background-color: #ffffff;
            color: #1e293b;
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #0f172a;
            color: #e0e0e0;
        }

        /* Container spacing fix */
        .container {
            max-width: 1000px;
            margin: 100px auto 40px; /* Added top margin to separate from navbar */
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .confirmation-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
            margin-bottom: 2rem;
            animation: fadeUp 0.6s ease-out;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(46, 204, 113, 0); }
            100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
        }

        .success-icon i {
            color: white;
            font-size: 50px;
        }

        .success-header h1 {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--primary-color);
        }

        .success-header p {
            color: #555;
            max-width: 600px;
            margin: 0 auto;
        }

        .receipt-container {
            margin: 2.5rem 0;
            border: 2px dashed #e0e0e0;
            border-radius: var(--border-radius);
            padding: 2rem;
            position: relative;
            background: #fcfcfc;
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .receipt-logo {
            display: flex;
            align-items: center;
        }

        .receipt-logo h3 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
        }

        .receipt-logo span {
            color: var(--secondary-color);
        }

        .receipt-info {
            text-align: right;
        }

        .receipt-info h4 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .receipt-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--success-color);
            color: white;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .receipt-section {
            margin-bottom: 2rem;
        }

        .receipt-section h4 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .receipt-item:last-child {
            border-bottom: none;
        }

        .receipt-label {
            color: #666;
        }

        .receipt-value {
            font-weight: 500;
        }

        .receipt-total {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e0e0e0;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
        }

        .receipt-total .receipt-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .receipt-total .receipt-value {
            font-weight: 700;
            color: var(--primary-color);
        }

        .car-summary {
            display: flex;
            gap: 1.5rem;
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            align-items: center;
        }

        .car-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .car-details {
            flex: 1;
        }

        .car-details h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1.25rem;
        }

        .car-features {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.75rem;
        }

        .car-feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #555;
        }

        .car-feature i {
            color: var(--secondary-color);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 2.5rem;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 1.75rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            text-align: center;
            flex: 1;
            min-width: 180px;
            max-width: 250px;
        }

        .btn i {
            font-size: 1.2rem;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: white;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.2);
            border: none;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 6px 10px rgba(52, 152, 219, 0.3);
        }

        .btn-outline {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 10px rgba(52, 152, 219, 0.2);
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 5rem;
            font-weight: 700;
            color: rgba(52, 152, 219, 0.1);
            pointer-events: none;
            white-space: nowrap;
        }

        .contact-section {
            background: white;
            padding: 3rem 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-top: 3rem;
            text-align: center;
        }

        .contact-section h2 {
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .contact-methods {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
        }

        .contact-method {
            flex: 1;
            min-width: 200px;
            max-width: 300px;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            text-align: center;
            transition: var(--transition);
        }

        .contact-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .contact-icon i {
            color: white;
            font-size: 24px;
        }

        .contact-method h4 {
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        .contact-method p {
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .contact-link {
            color: var(--secondary-color);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .contact-link:hover {
            color: #2980b9;
        }

        .contact-link i {
            font-size: 0.85rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 0 15px;
            }

            .confirmation-card {
                padding: 1.5rem;
            }

            .receipt-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .receipt-info {
                text-align: left;
                margin-top: 1rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                max-width: 100%;
                width: 100%;
            }

            .car-summary {
                flex-direction: column;
                align-items: flex-start;
            }

            .car-image {
                width: 100%;
                max-width: 250px;
                height: auto;
                margin: 0 auto;
            }

            .contact-methods {
                flex-direction: column;
                gap: 1rem;
            }

            .contact-method {
                max-width: 100%;
            }
        }

        .error-container {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .error-icon {
            width: 90px;
            height: 90px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
        }
        
        .error-icon i {
            color: white;
            font-size: 45px;
        }

        /* Print-friendly styles */
        @media print {
            body {
                background-color: white;
                font-size: 12pt;
            }
            
            header, .contact-section, .action-buttons, .success-header p {
                display: none;
            }
            
            .container, .confirmation-card, .receipt-container {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
            }
            
            .success-header {
                margin-bottom: 1rem;
            }
            
            .success-icon {
                width: 50px;
                height: 50px;
                margin-bottom: 0.5rem;
                animation: none;
                box-shadow: none;
            }
            
            .success-icon i {
                font-size: 25px;
            }
            
            .watermark {
                display: none;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body.dark-mode {
                --background-color: #121212;
                --text-color: #e0e0e0;
                --primary-color: #e0e0e0;
                color: var(--text-color);
                background-color: var(--background-color);
            }
            
            body.dark-mode .confirmation-card,
            body.dark-mode .receipt-container,
            body.dark-mode .contact-section,
            body.dark-mode .car-summary {
                background: #172e53;
                border-color: #ffffff;
            }
            
            body.dark-mode .receipt-item {
                border-color: #333;
            }
            
            body.dark-mode .receipt-total {
                border-color: #444;
            }
            
            body.dark-mode .contact-method {
                background: #172e53;
            }
            
            body.dark-mode .receipt-label {
                color: #aaa;
            }
            
            body.dark-mode .btn-outline {
                border-color: var(--secondary-color);
                color: var(--secondary-color);
            }
        }
        
        /* Dark mode toggle */
        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
        }
        
        .dark-mode-toggle i {
            color: white;
            font-size: 20px;
        }
        
        .dark-mode-toggle:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                                <a href="about.php#contact" class="nav-link">Contact</a>
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
                    <?php else: ?>
                        <a href="data/authentication.php?action=login" class="nav-btn login-btn">Login</a>
                        <a href="data/authentication.php?action=register" class="nav-btn signup-btn">Sign Up</a>
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

    <!-- Main Content -->
    <div class="container">
        <?php if (isset($noBookingData)): ?>
            <div class="confirmation-card">
                <div class="error-container">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1>No Booking Information Found</h1>
                    <p>We couldn't find your booking information. Please try making a reservation again.</p>
                    
                    <div class="action-buttons" style="margin-top: 2rem;">
                        <a href="book.php" class="btn btn-primary">
                            <i class="fas fa-car"></i> Browse Available Cars
                        </a>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-home"></i> Return to Home
                        </a>
                    </div>
                </div>
            </div>
        <?php elseif ($booking): ?>
            <div class="confirmation-card">
                <div class="success-header">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h1>Booking Successfully Confirmed!</h1>
                    <p>Thank you for choosing CARSRENT. Your booking has been processed and is now confirmed. We've prepared your receipt below.</p>
                </div>

                <div class="receipt-container">
                    <div class="watermark">RECEIPT</div>
                    
                    <div class="receipt-header">
                        <div class="receipt-logo">
                            <h3><span>CARS</span>RENT</h3>
                        </div>
                        <div class="receipt-info">
                            <h4>Booking Receipt</h4>
                            <p>Date: <?php echo date('F j, Y'); ?></p>
                            <p>Reference #: <?php echo str_pad($bookingId, 6, '0', STR_PAD_LEFT); ?></p>
                            <div class="receipt-badge">
                                <i class="fas fa-check-circle"></i> Confirmed
                            </div>
                        </div>
                    </div>

                    <div class="car-summary">
                        <?php 
                        // Try multiple approaches to find a car image
                        $imagePath = './images/car-placeholder.png'; // Default fallback
                        
                        // First check primary_image from car_images table if available
                        if (!empty($booking['car']['primary_image'])) {
                            if (file_exists($booking['car']['primary_image'])) {
                                $imagePath = $booking['car']['primary_image'];
                            } else if (file_exists('./' . $booking['car']['primary_image'])) {
                                $imagePath = './' . $booking['car']['primary_image'];
                            }
                        }
                        
                        // If primary image not found, try the legacy image
                        if ($imagePath == './images/car-placeholder.png' && !empty($booking['car']['image'])) {
                            if (file_exists($booking['car']['image'])) {
                                $imagePath = $booking['car']['image'];
                            } else if (file_exists('./' . $booking['car']['image'])) {
                                $imagePath = './' . $booking['car']['image'];
                            }
                        }
                        
                        // Try looking directly in the car_id folder
                        if ($imagePath == './images/car-placeholder.png' && !empty($booking['car']['id'])) {
                            $carFolder = './images/cars/' . $booking['car']['id'] . '/';
                            if (is_dir($carFolder)) {
                                $files = glob($carFolder . '*.{jpg,jpeg,png}', GLOB_BRACE);
                                if (!empty($files)) {
                                    $imagePath = $files[0];
                                }
                            }
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($booking['car']['name']); ?>" class="car-image">
                        <div class="car-details">
                            <h4><?php echo htmlspecialchars($booking['car']['name']); ?></h4>
                            <p><?php echo htmlspecialchars($booking['car']['brand']); ?> - <?php echo htmlspecialchars($booking['car']['model']); ?></p>
                            <div class="car-features">
                                <div class="car-feature">
                                    <i class="fas fa-cog"></i>
                                    <span><?php echo htmlspecialchars($booking['car']['transmission']); ?></span>
                                </div>
                                <div class="car-feature">
                                    <i class="fas fa-chair"></i>
                                    <span><?php echo htmlspecialchars($booking['car']['interior'] ?? 'Standard'); ?> Interior</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="receipt-section">
                        <h4>Customer Information</h4>
                        <div class="receipt-item">
                            <span class="receipt-label">Name:</span>
                            <span class="receipt-value"><?php echo htmlspecialchars($booking['username']); ?></span>
                        </div>
                        <div class="receipt-item">
                            <span class="receipt-label">Email:</span>
                            <span class="receipt-value"><?php echo htmlspecialchars($booking['email']); ?></span>
                        </div>
                        <div class="receipt-item">
                            <span class="receipt-label">Phone:</span>
                            <span class="receipt-value"><?php echo htmlspecialchars($booking['phone']); ?></span>
                        </div>
                    </div>

                    <div class="receipt-section">
                        <h4>Rental Details</h4>
                        <div class="receipt-item">
                            <span class="receipt-label">Pick-up Date:</span>
                            <span class="receipt-value"><?php echo date('F j, Y', strtotime($booking['startDate'])); ?></span>
                        </div>
                        <div class="receipt-item">
                            <span class="receipt-label">Return Date:</span>
                            <span class="receipt-value"><?php echo date('F j, Y', strtotime($booking['endDate'])); ?></span>
                        </div>
                        <div class="receipt-item">
                            <span class="receipt-label">Duration:</span>
                            <span class="receipt-value"><?php echo $booking['duration']; ?> days</span>
                        </div>
                    </div>

                    <div class="receipt-section">
                        <h4>Payment Summary</h4>
                        <div class="receipt-item">
                            <span class="receipt-label">Vehicle Rate:</span>
                            <span class="receipt-value">$<?php echo number_format($booking['car']['price'], 2); ?> × <?php echo $booking['duration']; ?> days</span>
                        </div>
                        <div class="receipt-item">
                            <span class="receipt-label">Vehicle Subtotal:</span>
                            <span class="receipt-value">$<?php echo number_format($booking['car']['price'] * $booking['duration'], 2); ?></span>
                        </div>
                        <div class="receipt-item">
                            <span class="receipt-label">Insurance Fee:</span>
                            <span class="receipt-value">$25.00</span>
                        </div>
                        <?php if (isset($booking['status']) && $booking['status'] === 'preorder'): ?>
                        <div class="receipt-item">
                            <span class="receipt-label">Preorder Fee:</span>
                            <span class="receipt-value">$15.00</span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($paymentDetails)): ?>
                        <div class="receipt-item">
                            <span class="receipt-label">Transaction ID:</span>
                            <span class="receipt-value"><?php echo htmlspecialchars($paymentDetails['transaction_id']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="receipt-item">
                            <span class="receipt-label">Payment Method:</span>
                            <span class="receipt-value"><?php echo ucfirst(htmlspecialchars($booking['paymentMethod'])); ?></span>
                        </div>
                        <div class="receipt-total">
                            <span class="receipt-label">Total Amount Paid:</span>
                            <span class="receipt-value">$<?php echo number_format($booking['totalAmount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <?php if ($bookingId > 0): ?>
                    <a href="generate_invoice.php?booking_id=<?php echo $bookingId; ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf"></i> Download Invoice
                    </a>
                    <?php else: ?>
                    <a href="generate_invoice.php" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf"></i> Download Invoice
                    </a>
                    <?php endif; ?>
                    <a href="book.php" class="btn btn-outline">
                        <i class="fas fa-car"></i> Browse More Cars
                    </a>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Return to Home
                    </a>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i>
                    A confirmation email has been sent to your email address with all the details of your booking.
                </div>
            </div>

            <!-- Contact Section -->
            <div class="contact-section" id="contact">
                <h2>Need Assistance?</h2>
                <p>If you have any questions about your booking or need to make changes, our support team is here to help.</p>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h4>Call Us</h4>
                        <p>Our customer service is available 24/7 to assist you.</p>
                        <a href="tel:+18001234567" class="contact-link">+1 (800) 123-4567 <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email Support</h4>
                        <p>Send us an email and we'll respond within 24 hours.</p>
                        <a href="mailto:support@carsrent.com" class="contact-link">support@carsrent.com <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Visit Us</h4>
                        <p>Come to our main office for in-person assistance.</p>
                        <a href="about.php#contact" class="contact-link">Find our locations <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <a href="about.php#contact" class="btn btn-outline" style="margin-top: 1rem;">
                    <i class="fas fa-comments"></i> Contact Us
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dark mode toggle -->
    <div class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </div>

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

            // Print functionality for receipt
            const printButton = document.createElement('button');
            printButton.className = 'btn btn-outline';
            printButton.innerHTML = '<i class="fas fa-print"></i> Print Receipt';
            printButton.addEventListener('click', function() {
                window.print();
            });

            // Add print button to action buttons
            const actionButtons = document.querySelector('.action-buttons');
            if (actionButtons) {
                actionButtons.appendChild(printButton);
            }

            // Dark mode toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            
            // Initialize dark mode based on local storage
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
            
            darkModeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                const isDark = document.body.classList.contains('dark-mode');
                localStorage.setItem('darkMode', isDark);
                
                if (isDark) {
                    darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                } else {
                    darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                }
            });
        });
    </script>
</body>
</html>