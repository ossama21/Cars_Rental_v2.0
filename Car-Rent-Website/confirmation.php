<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "car_rent";

// Redirect if not logged in
if (!isset($_SESSION['firstName'])) {
    header("Location: data/index.php");
    exit();
}

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$booking = null;
$bookingId = 0;
$bookingConfirmed = false;

// Handle direct POST form submissions from checkout.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    try {
        $payment_method = $_POST['payment_method'] ?? 'unknown';
        $payment_data = isset($_POST['payment_data']) ? $_POST['payment_data'] : '{}';
        $name = $_POST['firstName'] . ' ' . $_POST['lastName'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $car_id = intval($_POST['car_id']);
        $start_date = $_POST['startDate'];
        $end_date = $_POST['endDate'];
        
        // Get car details
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $car = $result->fetch_assoc();
        $stmt->close();
        
        if (!$car) {
            throw new Exception('Car not found');
        }
        
        // Calculate duration and total amount
        $startDateObj = new DateTime($start_date);
        $endDateObj = new DateTime($end_date);
        $duration = max(1, $startDateObj->diff($endDateObj)->days);
        $totalAmount = ($duration * $car['price']) + 25; // Adding $25 insurance fee

        // Begin transaction
        $conn->begin_transaction();
        
        // Insert into services table
        $stmt = $conn->prepare("INSERT INTO services (
            username, phone, start_date, end_date, duration,
            email, amount, car_id, payment_method, payment_details
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param(
            "ssssisdiss",
            $name,
            $phone,
            $start_date,
            $end_date,
            $duration,
            $email,
            $totalAmount,
            $car_id,
            $payment_method,
            $payment_data
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save booking: " . $stmt->error);
        }
        
        $bookingId = $conn->insert_id;
        
        // Insert into payments table
        $stmt = $conn->prepare("INSERT INTO payments (
            booking_id, method, amount, status, transaction_id
        ) VALUES (?, ?, ?, 'completed', ?)");
        
        $transactionId = 'TXN' . time() . rand(1000, 9999);
        $stmt->bind_param("isds", $bookingId, $payment_method, $totalAmount, $transactionId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to record payment: " . $stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Store booking in session
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
            'totalAmount' => $totalAmount,
            'bookingId' => $bookingId
        ];
        
        $booking = $_SESSION['booking'];
        $bookingConfirmed = true;
        
    } catch (Exception $e) {
        if ($conn && $conn->ping()) {
            $conn->rollback();
        }
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}
// Check if booking data is in session (from AJAX submission)
elseif (isset($_SESSION['booking'])) {
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
// If no booking data available, show error message instead of redirecting
else {
    $noBookingData = true;
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

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
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
            width: 90px;
            height: 90px;
            background: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }

        .success-icon i {
            color: white;
            font-size: 45px;
        }

        .success-header h1 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--background-color);
            border-radius: var(--border-radius);
        }

        .detail-section {
            margin-bottom: 1rem;
        }
        
        .detail-section h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
            position: relative;
            padding-left: 1.5rem;
        }
        
        .detail-section h3 i {
            position: absolute;
            left: 0;
            top: 0.2rem;
            color: var(--secondary-color);
        }

        .detail-group {
            margin-bottom: 0.75rem;
        }

        .detail-group label {
            display: block;
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .detail-group span {
            font-weight: 500;
            color: var(--text-color);
        }

        .car-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            background: white;
            border-radius: var(--border-radius);
            margin: 1.5rem 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .car-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .car-details h3 {
            font-size: 1.3rem;
            margin-bottom: 0.25rem;
        }

        .car-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .car-feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .car-feature i {
            color: var(--secondary-color);
        }

        .price-summary {
            background: var(--primary-color);
            color: white;
            padding: 1.75rem;
            border-radius: var(--border-radius);
            margin-top: 2rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .total-price {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 1rem;
            padding-top: 1rem;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 2.5rem;
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
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
            flex: 1;
            min-width: 180px;
        }

        .btn i {
            font-size: 1.2rem;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: white;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.2);
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

        .confirmation-notes {
            padding: 1.5rem;
            margin-top: 2rem;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            position: relative;
        }

        .confirmation-notes h3 {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .confirmation-notes p {
            margin-bottom: 1rem;
            color: #555;
            font-size: 0.95rem;
        }

        .confirmation-notes ul {
            padding-left: 1.25rem;
            margin-bottom: 1rem;
        }

        .confirmation-notes li {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .confirmation-badge {
            position: absolute;
            top: -10px;
            left: 20px;
            padding: 0.25rem 1rem;
            background: var(--success-color);
            color: white;
            font-weight: 500;
            border-radius: 50px;
            font-size: 0.85rem;
        }

        .navbar {
            background-color: #1a202c !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .container {
            margin-top: 120px;
            position: relative;
            z-index: 1;
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

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
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
                    <h1>Booking Confirmed!</h1>
                    <p>Thank you for choosing CARSRENT. Your booking has been successfully processed.</p>
                    <?php if ($bookingId > 0): ?>
                    <div class="booking-id" style="margin-top: 10px; font-size: 0.9rem; color: #666;">
                        <span>Booking Reference: <strong>#<?php echo $bookingId; ?></strong></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="car-info">
                    <img src="<?php echo htmlspecialchars($booking['car']['image']); ?>" alt="<?php echo htmlspecialchars($booking['car']['name']); ?>" class="car-image">
                    <div class="car-details">
                        <h3><?php echo htmlspecialchars($booking['car']['name']); ?></h3>
                        <p><?php echo htmlspecialchars($booking['car']['brand']); ?> - <?php echo htmlspecialchars($booking['car']['model']); ?></p>
                        <div class="car-features">
                            <div class="car-feature">
                                <i class="fas fa-cog"></i>
                                <span><?php echo htmlspecialchars($booking['car']['transmission']); ?> Transmission</span>
                            </div>
                            <div class="car-feature">
                                <i class="fas fa-chair"></i>
                                <span><?php echo htmlspecialchars($booking['car']['interior']); ?> Interior</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="booking-details">
                    <div class="detail-section">
                        <h3><i class="fas fa-user"></i> Personal Information</h3>
                        <div class="detail-group">
                            <label>Name</label>
                            <span><?php echo htmlspecialchars($booking['username']); ?></span>
                        </div>
                        <div class="detail-group">
                            <label>Email</label>
                            <span><?php echo htmlspecialchars($booking['email']); ?></span>
                        </div>
                        <div class="detail-group">
                            <label>Phone</label>
                            <span><?php echo htmlspecialchars($booking['phone']); ?></span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3><i class="fas fa-calendar-alt"></i> Rental Period</h3>
                        <div class="detail-group">
                            <?php if ($booking['status'] === 'preorder'): ?>
                                <div class="preorder-badge">
                                    <i class="fas fa-clock"></i> Pre-ordered
                                </div>
                            <?php endif; ?>
                            <label>Pick-up Date</label>
                            <span><?php echo date('F j, Y g:i A', strtotime($booking['startDate'])); ?></span>
                        </div>
                        <div class="detail-group">
                            <label>Return Date</label>
                            <span><?php echo date('F j, Y g:i A', strtotime($booking['endDate'])); ?></span>
                        </div>
                        <div class="detail-group">
                            <label>Duration</label>
                            <span><?php echo $booking['duration']; ?> days</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3><i class="fas fa-money-bill-wave"></i> Payment Details</h3>
                        <div class="detail-group">
                            <label>Base Rate</label>
                            <span>$<?php echo number_format($booking['car']['price'] * $booking['duration'], 2); ?></span>
                        </div>
                        <div class="detail-group">
                            <label>Insurance Fee</label>
                            <span>$25.00</span>
                        </div>
                        <?php if ($booking['status'] === 'preorder'): ?>
                            <div class="detail-group">
                                <label>Preorder Fee</label>
                                <span>$15.00</span>
                            </div>
                        <?php endif; ?>
                        <div class="detail-group total">
                            <label>Total Amount</label>
                            <span>$<?php echo number_format($booking['totalAmount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="price-summary">
                    <div class="price-row">
                        <span>Daily Rate</span>
                        <span>$<?php echo number_format($booking['car']['price'], 2); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Duration</span>
                        <span><?php echo htmlspecialchars($booking['duration']); ?> day<?php echo $booking['duration'] > 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="price-row">
                        <span>Insurance Fee</span>
                        <span>$25.00</span>
                    </div>
                    <div class="price-row">
                        <span>Payment Method</span>
                        <span><?php echo ucfirst(htmlspecialchars($booking['paymentMethod'])); ?></span>
                    </div>
                    <div class="price-row total-price">
                        <span>Total Amount</span>
                        <span>$<?php echo number_format($booking['totalAmount'], 2); ?></span>
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
                        <i class="fas fa-car"></i> Explore More Cars
                    </a>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Return to Home
                    </a>
                </div>
                
                <div class="confirmation-notes">
                    <div class="confirmation-badge">Important</div>
                    <h3>Rental Notes</h3>
                    <p>Please remember the following information for your rental:</p>
                    <ul>
                        <li>Bring your driver's license and a valid ID for vehicle pick-up</li>
                        <li>The security deposit will be refunded upon vehicle return (subject to condition check)</li>
                        <li>A confirmation email has been sent to your registered email address</li>
                        <li>For any changes to your booking, please contact our customer service at least 24 hours before pick-up</li>
                    </ul>
                    <p>Thank you for choosing CARSRENT! We hope you enjoy your rental experience.</p>
                </div>
            </div>
        <?php endif; ?>
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
        });
    </script>
</body>
</html>
