<?php
session_start();
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$host="localhost";
$user="root";
$pass="";
$db="car_rent";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$pickup_date = isset($_GET['pickup_date']) ? $_GET['pickup_date'] : '';
$return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';

// Get all unique brands for filtering
$brandQuery = "SELECT DISTINCT brand FROM cars";
$brandResult = $conn->query($brandQuery);
$brands = [];
while($row = $brandResult->fetch_assoc()) {
    $brands[] = $row['brand'];
}

// Build the base query with discount information
$sql = "SELECT c.*, 
        (SELECT COUNT(*) 
         FROM services s 
         WHERE s.car_id = c.id 
         AND (
             CURRENT_DATE BETWEEN DATE(s.start_date) AND DATE(s.end_date)
             OR DATE(s.start_date) >= CURRENT_DATE
         )
        ) as active_rentals,
        d.discount_type,
        d.discount_value,
        d.end_date as discount_end,
        CASE 
            WHEN d.discount_type = 'percentage' THEN CONCAT(d.discount_value, '%')
            WHEN d.discount_type = 'fixed' THEN CONCAT('$', d.discount_value)
            ELSE NULL 
        END as discount_display,
        CASE 
            WHEN d.discount_type = 'percentage' THEN c.price * (1 - d.discount_value/100)
            WHEN d.discount_type = 'fixed' THEN c.price - d.discount_value
            ELSE c.price 
        END as discounted_price
        FROM cars c 
        LEFT JOIN car_discounts d ON c.id = d.car_id 
            AND CURRENT_TIMESTAMP BETWEEN d.start_date AND d.end_date 
            AND d.end_date > CURRENT_TIMESTAMP
        WHERE 1=1";

// Add search conditions
if (!empty($type)) {
    switch(strtolower($type)) {
        case 'sedan':
            $sql .= " AND (LOWER(c.name) LIKE '%sedan%' OR LOWER(c.description) LIKE '%sedan%')";
            break;
        case 'suv':
            $sql .= " AND (LOWER(c.name) LIKE '%suv%' OR LOWER(c.description) LIKE '%suv%')";
            break;
        case 'luxury':
            $sql .= " AND (LOWER(c.name) LIKE '%luxury%' OR LOWER(c.description) LIKE '%luxury%' OR c.price >= 200)";
            break;
        case 'sports':
            $sql .= " AND (LOWER(c.name) LIKE '%sport%' OR LOWER(c.description) LIKE '%sport%')";
            break;
    }
}

// Add date availability check if dates are provided
if (!empty($pickup_date) && !empty($return_date)) {
    $sql .= " AND c.id NOT IN (
        SELECT DISTINCT car_id 
        FROM services 
        WHERE (
            ('$pickup_date' BETWEEN DATE(start_date) AND DATE(end_date))
            OR ('$return_date' BETWEEN DATE(start_date) AND DATE(end_date))
            OR (DATE(start_date) BETWEEN '$pickup_date' AND '$return_date')
        )
    )";
}

$result = $conn->query($sql);
$cars = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode(['cars' => $cars]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Available Cars - CARSRENT</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="./images/image.png">
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="./css/book.css">
    <link rel="stylesheet" href="./css/modern.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --background-light: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #95a5a6;
            --shadow-color: rgba(0,0,0,0.1);
            --card-radius: 15px;
            --transition-speed: 0.3s;
        }

        /* Add transparent navbar initial state */
        .navbar {
            background: #5d5f5d !important;
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1030;
            transition: all 0.4s ease;
        }

        .navbar.scrolled {
            background: white !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 2rem;
        }

        /* Update text colors for transparent navbar */
        .navbar-brand, .nav-link {
            color: white !important;
            transition: color 0.3s ease;
        }

        .navbar.scrolled .navbar-brand, 
        .navbar.scrolled .nav-link {
            color: var(--text-dark) !important;
        }

        .login-btn {
            color: white !important;
            border-color: white !important;
        }

        .navbar.scrolled .login-btn {
            color: var(--text-dark) !important;
            border-color: var(--text-dark) !important;
        }

        .navbar .menu-toggle span {
            background: white;
        }

        .navbar.scrolled .menu-toggle span {
            background: var(--text-dark);
        }

        .main-content {
            padding-top: 100px;
            min-height: calc(100vh - 80px);
            background: linear-gradient(135deg, var(--background-light) 0%, #ffffff 100%);
        }

        .filters-section {
            background: white;
            padding: 2rem;
            border-radius: var(--card-radius);
            margin-bottom: 3rem;
            box-shadow: 0 5px 20px var(--shadow-color);
            position: sticky;
            top: 90px;
            z-index: 100;
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .form-control {
            height: 50px !important;
            border-radius: 25px !important;
            padding-left: 45px !important;
            border: 2px solid #eee !important;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.1) !important;
        }

        #car-listings {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            padding: 1rem 0;
        }

        .car-card {
            background: white;
            border-radius: var(--card-radius);
            overflow: hidden;
            box-shadow: 0 10px 20px var(--shadow-color);
            transition: all var(--transition-speed);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .car-image {
            height: 200px;
            position: relative;
            overflow: hidden;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .car-card:hover .car-image img {
            transform: scale(1.1);
        }

        .car-status {
            position: absolute;
            top: 15px;
            right: 15px;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 1;
        }

        .car-status.available {
            background: #2ecc71;
        }

        .car-status.rented {
            background: #e74c3c;
        }

        .car-details {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .car-brand {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .car-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .car-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .info-item i {
            margin-right: 8px;
            color: var(--secondary-color);
            font-size: 1rem;
        }

        .car-price {
            padding: 8px 12px;
            border-radius: 8px;
            margin: 8px 0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(52, 152, 219, 0.05);
            flex-wrap: wrap;
        }

        .price-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .discounted-price {
            color: #e74c3c;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1;
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 4px;
        }

        .discount-badge {
            background: #e74c3c;
            color: white;
            padding: 2px 6px;
            font-size: 0.65rem;
            font-weight: 600;
            border-radius: 8px;
            white-space: nowrap;
        }

        .original-price {
            font-size: 0.7rem;
            color: #95a5a6;
            text-decoration: line-through;
            line-height: 1;
            margin-top: 2px;
        }

        .regular-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .book-now-btn {
            margin-top: 12px;
            width: 100%;
            padding: 10px;
            text-align: center;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .book-now-btn:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }

        /* Add styles for discount tag popover */
        .discount-tag {
            cursor: pointer;
            position: relative;
        }

        .discount-popover {
            position: absolute;
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.2);
            padding: 15px;
            width: 220px;
            z-index: 100;
            display: none;
            top: calc(100% + 10px);
            left: 50%;
            transform: translateX(-50%);
        }

        .discount-popover::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 0 10px 10px 10px;
            border-style: solid;
            border-color: transparent transparent white transparent;
        }

        .discount-popover.show {
            display: block;
        }

        .rent-now.disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .preorder-section {
            background: rgba(52, 152, 219, 0.1);
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
            border: 1px solid rgba(52, 152, 219, 0.2);
        }

        .next-available {
            color: #2980b9;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .preorder-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
            text-align: center;
            width: 100%;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-bottom: 0.5rem;
        }

        .preorder-btn:hover {
            background: #2980b9;
            text-decoration: none;
            color: white;
        }

        .preorder-fee {
            display: block;
            color: #e67e22;
            font-size: 0.8rem;
            text-align: center;
        }

        .availability-badge {
            text-align: center;
            padding: 0.5rem;
            margin: 0.5rem 0;
            border-radius: 5px;
        }

        .availability-badge.unavailable {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .availability-badge i {
            margin-right: 0.5rem;
        }

        .page-title {
            padding: 3rem 0;
        }

        .subtitle {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .title-separator {
            width: 80px;
            height: 3px;
            background: var(--secondary-color);
            margin: 1.5rem auto;
        }

        .search-tag {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .search-tag .badge {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 20px;
        }

        .search-tag .badge i {
            margin-right: 0.5rem;
        }

        .bg-primary {
            background-color: var(--secondary-color) !important;
        }

        .bg-secondary {
            background-color: var(--text-light) !important;
        }

        .discount-badge {
            background: #e74c3c;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 8px;
            animation: pulse 2s infinite;
        }

        .original-price {
            font-size: 1rem;
            opacity: 0.7;
            margin-bottom: 4px;
        }

        .discounted-price {
            color: #e74c3c;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .discounted-price small {
            font-size: 1rem;
            opacity: 0.7;
        }

        .save-text {
            background: #2ecc71;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 8px;
            display: inline-block;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Update car-price styles */
        .car-price {
            padding: 12px;
            border-radius: 8px;
            background: rgba(52, 152, 219, 0.05);
            margin: 15px 0;
        }

        .discount-countdown {
            margin-top: 5px;
            font-size: 0.85rem;
        }

        .discount-countdown.urgent .countdown-text {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
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
                  <a href="book.php" class="nav-link active">Cars</a>
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="page-title text-center mb-5" data-aos="fade-up">
                <span class="subtitle text-primary mb-2 d-block">Available Cars</span>
                <h1 class="display-4 fw-bold mb-3">Discover Your Perfect Drive</h1>
                <div class="title-separator mx-auto my-3"></div>
                <p class="lead text-muted">Explore our extensive collection of premium vehicles tailored to your needs</p>
                <?php if (!empty($type)): ?>
                    <div class="search-tag mt-3">
                        <span class="badge bg-primary">
                            <i class="fas fa-car me-1"></i> 
                            <?php echo ucfirst(htmlspecialchars($type)); ?> Cars
                        </span>
                        <?php if (!empty($pickup_date) && !empty($return_date)): ?>
                            <span class="badge bg-secondary ms-2">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('M d', strtotime($pickup_date)); ?> - <?php echo date('M d', strtotime($return_date)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by name, brand or model...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="brandFilter">
                            <option value="">All Brands</option>
                            <?php foreach($brands as $brand): ?>
                                <option value="<?php echo htmlspecialchars($brand); ?>"><?php echo htmlspecialchars($brand); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="sortBy">
                            <option value="name">Sort by Name</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Cars Grid -->
            <div id="car-listings">
                <?php if (empty($cars)): ?>
                <div class="no-cars-found">
                    <i class="fas fa-car-slash"></i>
                    <h3>No Cars Available</h3>
                    <p>Sorry, there are no cars matching your criteria at the moment.</p>
                </div>
                <?php else: ?>
                <?php foreach ($cars as $car): ?>
                <div class="car-card" data-brand="<?php echo htmlspecialchars($car['brand']); ?>">
                    <div class="car-image">
                        <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
                        <?php if ($car['active_rentals'] >= $car['quantity']): ?>
                            <div class="car-status rented">
                                <i class="fas fa-clock"></i> Rented
                            </div>
                            <?php 
                            // Get the earliest available date for this car
                            $stmt = $conn->prepare("SELECT MAX(end_date) as next_available FROM services WHERE car_id = ?");
                            $stmt->bind_param("i", $car['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $nextAvailable = $result->fetch_assoc()['next_available'];
                            $nextAvailableDate = new DateTime($nextAvailable);
                            $nextAvailableDate->modify('+1 day'); // Available from next day
                            ?>
                            <div class="preorder-section">
                                <p class="next-available">Next available from: <?php echo $nextAvailableDate->format('M d, Y'); ?></p>
                                <a href="checkout.php?car_id=<?php echo $car['id']; ?>&preorder=1&available_from=<?php echo $nextAvailableDate->format('Y-m-d'); ?>" 
                                   class="preorder-btn">
                                    <i class="fas fa-clock"></i> Preorder
                                </a>
                                <small class="preorder-fee">*Preorder fee: $15</small>
                            </div>
                        <?php else: ?>
                            <div class="car-status available">
                                <i class="fas fa-check"></i> Available
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="car-details">
                        <div class="car-brand"><?php echo htmlspecialchars($car['brand']); ?></div>
                        <h3 class="car-title"><?php echo htmlspecialchars($car['name']); ?></h3>
                        <div class="car-info">
                            <div class="info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo htmlspecialchars($car['model']); ?>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-cog"></i>
                                <?php echo htmlspecialchars($car['transmission']); ?>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-chair"></i>
                                <?php echo htmlspecialchars($car['interior']); ?>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-gas-pump"></i>
                                Petrol
                            </div>
                        </div>
                        <div class="car-price">
                            <?php if (isset($car['discount_display'])): ?>
                                <div class="price-content" data-original-price="<?php echo number_format($car['price'], 2); ?>">
                                    <div class="discounted-price">
                                        $<?php echo number_format($car['discounted_price'], 2); ?>
                                        <small>/day</small>
                                        <span class="discount-badge"><?php echo str_replace(' OFF', '', $car['discount_display']); ?></span>
                                    </div>
                                    <div class="original-price">was $<?php echo number_format($car['price'], 2); ?></div>
                                    <?php if (isset($car['discount_end'])): ?>
                                    <div class="discount-countdown" data-end="<?php echo $car['discount_end']; ?>">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> Offer ends in: <span class="countdown-text">Loading...</span>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="price-content">
                                    <div class="regular-price">
                                        $<?php echo number_format($car['price'], 2); ?><small>/day</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!$car['active_rentals'] >= $car['quantity']): ?>
                            <?php if (isset($_SESSION['firstName'])): ?>
                                <a href="checkout.php?car_id=<?php echo $car['id']; ?><?php echo isset($car['discounted_price']) ? '&price=' . $car['discounted_price'] : ''; ?>" class="book-now-btn">Book Now</a>
                            <?php else: ?>
                                <a href="data/index.php" class="book-now-btn">Login to Book</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php 
                            // Get the earliest available date for this car
                            $stmt = $conn->prepare("SELECT MAX(end_date) as next_available FROM services WHERE car_id = ?");
                            $stmt->bind_param("i", $car['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $nextAvailable = $result->fetch_assoc()['next_available'];
                            $nextAvailableDate = new DateTime($nextAvailable);
                            $nextAvailableDate->modify('+1 day'); // Available from next day
                            ?>
                            <div class="preorder-section">
                                <p class="next-available">Next available from: <?php echo $nextAvailableDate->format('M d, Y'); ?></p>
                                <a href="checkout.php?car_id=<?php echo $car['id']; ?>&preorder=1&available_from=<?php echo $nextAvailableDate->format('Y-m-d'); ?>" 
                                   class="preorder-btn">
                                    <i class="fas fa-clock"></i> Preorder
                                </a>
                                <small class="preorder-fee">*Preorder fee: $15</small>
                            </div>
                            <div class="availability-badge unavailable">
                                <i class="fas fa-times-circle"></i> Currently Unavailable
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer id="contact">
      <div class="footer-content">
        <div class="container">
          <div class="row">
            <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
              <a style="font-weight: 900;" href="#" class="footer-brand">
                <span class="brand-highlight">CARS</span>RENT
              </a>
              <p class="mt-3">Providing quality car rentals with exceptional service. Best cars at competitive prices.</p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
              </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
              <h5>Quick Links</h5>
              <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="./book.php">Book Now</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="#contact">Contact Us</a></li>
              </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
              <h5>Contact Info</h5>
              <ul class="contact-info">
                <li><i class="fas fa-map-marker-alt"></i> Morocco CasaBlanca, City</li>
                <li><i class="fas fa-phone"></i> +212 0678963254</li>
                <li><i class="fas fa-envelope"></i> support@carsrent.com</li>
              </ul>
            </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
              <h5>TEAM</h5>
              <div class="team-info">
                <a href="https://github.com/ossama21/Cars_Rental_WebSite-Project" class="github-link">
                  <i class="fab fa-github"></i> Cars_Rental_WebSite-Project
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-6">
              <p class="mb-0">&copy;<span id="currentYear"></span> <span>CARS</span>RENT - All Rights Reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
              <p class="mb-0">Made by: Mohammed Ali & Oussama</p>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add navbar scroll behavior
            function checkScroll() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }

            // Initial check and add scroll listener
            checkScroll();
            window.addEventListener('scroll', checkScroll);

            // Set current year in footer
            document.getElementById('currentYear').textContent = new Date().getFullYear();

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
            
            // Mobile menu toggle
            const menuToggle = document.querySelector('.menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            
            if (menuToggle) {
              menuToggle.addEventListener('click', function() {
                menuToggle.classList.toggle('active');
                navMenu.classList.toggle('active');
                document.body.classList.toggle('menu-open');
              });
            }

            // Search and filter functionality
            const searchInput = document.getElementById('searchInput');
            const brandFilter = document.getElementById('brandFilter');
            const sortBy = document.getElementById('sortBy');
            const carItems = document.querySelectorAll('.car-item');

            function filterCars() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedBrand = brandFilter.value.toLowerCase();

                carItems.forEach(item => {
                    const carName = item.querySelector('h4').textContent.toLowerCase();
                    const carBrand = item.dataset.brand.toLowerCase();
                    const shouldShow = 
                        carName.includes(searchTerm) && 
                        (selectedBrand === '' || carBrand === selectedBrand);
                    
                    item.style.display = shouldShow ? 'block' : 'none';
                });
            }

            function sortCars() {
                const carsList = document.getElementById('car-listings');
                const cars = Array.from(carItems);
                
                cars.sort((a, b) => {
                    const valueA = a.querySelector('h4').textContent;
                    const valueB = b.querySelector('h4').textContent;
                    
                    if (sortBy.value === 'price-low') {
                        return parseFloat(a.querySelector('.car-price').textContent.replace('$', '')) - 
                               parseFloat(b.querySelector('.car-price').textContent.replace('$', ''));
                    } else if (sortBy.value === 'price-high') {
                        return parseFloat(b.querySelector('.car-price').textContent.replace('$', '')) - 
                               parseFloat(a.querySelector('.car-price').textContent.replace('$', ''));
                    }
                    return valueA.localeCompare(valueB);
                });

                cars.forEach(car => carsList.appendChild(car));
            }

            searchInput.addEventListener('input', filterCars);
            brandFilter.addEventListener('change', filterCars);
            sortBy.addEventListener('change', sortCars);

            // Make discount tag elements interactive
            const discountBadges = document.querySelectorAll('.discount-badge');
            
            discountBadges.forEach(badge => {
                // Add discount-tag class to make them interactive
                badge.classList.add('discount-tag');
                
                // Create popover element for each badge
                const popover = document.createElement('div');
                popover.className = 'discount-popover';
                
                // Get discount information
                const discountText = badge.textContent.trim();
                const originalPrice = badge.closest('.price-content').dataset.originalPrice;
                const discountedPrice = badge.closest('.discounted-price').textContent.trim().split('/')[0].trim();
                const endDate = badge.closest('.price-content').querySelector('.discount-countdown')?.dataset.end;
                
                // Calculate savings
                const savings = parseFloat(originalPrice) - parseFloat(discountedPrice.replace('$', ''));
                
                // Build popover content
                popover.innerHTML = `
                    <h5 class="mb-2">Special Offer!</h5>
                    <p><strong>${discountText} discount</strong> applied to this car rental.</p>
                    <p class="mb-1">You save: <span class="text-success">$${savings.toFixed(2)}</span></p>
                    ${endDate ? `<small class="text-muted">Offer valid until ${new Date(endDate).toLocaleDateString()}</small>` : ''}
                `;
                
                // Add popover to the DOM
                badge.appendChild(popover);
                
                // Toggle popover on click
                badge.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent event bubbling
                    this.querySelector('.discount-popover').classList.toggle('show');
                });
            });
            
            // Close popovers when clicking elsewhere
            document.addEventListener('click', function() {
                document.querySelectorAll('.discount-popover.show').forEach(popup => {
                    popup.classList.remove('show');
                });
            });

            // Update all countdown timers
            const countdowns = document.querySelectorAll('.discount-countdown');
            
            function updateCountdown() {
                countdowns.forEach(countdown => {
                    const endDate = new Date(countdown.dataset.end);
                    const now = new Date();
                    const diff = endDate - now;
                    
                    if (diff <= 0) {
                        // Remove the entire discount display when expired
                        const priceContent = countdown.closest('.price-content');
                        if (priceContent) {
                            const regularPrice = document.createElement('div');
                            regularPrice.className = 'regular-price';
                            regularPrice.innerHTML = `$${priceContent.dataset.originalPrice}<small>/day</small>`;
                            priceContent.replaceWith(regularPrice);
                        }
                        return;
                    }
                    
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    
                    let text = '';
                    if (days > 0) {
                        text = `${days}d ${hours}h`;
                    } else if (hours > 0) {
                        text = `${hours}h ${minutes}m`;
                    } else {
                        text = `${minutes}m`;
                    }
                    
                    countdown.querySelector('.countdown-text').textContent = text;
                    
                    // Add urgent class if less than 24 hours remaining
                    if (diff < (1000 * 60 * 60 * 24)) {
                        countdown.classList.add('urgent');
                    }
                });
            }
            
            // Update countdown every minute
            updateCountdown();
            setInterval(updateCountdown, 60000);
        });
    </script>
</body>
</html>
<?php
// Move connection close to the end of the file
$conn->close();
?>
