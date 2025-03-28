<?php
session_start();
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

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

// Build the base query with discount information and primary image
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
        END as discounted_price,
        ci.image_path as primary_image
        FROM cars c 
        LEFT JOIN car_discounts d ON c.id = d.car_id 
            AND CURRENT_TIMESTAMP BETWEEN d.start_date AND d.end_date 
            AND d.end_date > CURRENT_TIMESTAMP
        LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_primary = 1
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
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $dir; ?>">
<head>
    <title><?php echo $lang['premium_cars']; ?> - CARSRENT</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="./images/image.png">
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="./css/book.css">
    <link rel="stylesheet" href="./css/modern.css">
    <link rel="stylesheet" href="./css/dark-mode.css">
    <!-- Language selector CSS -->
    <link rel="stylesheet" href="./css/language-selector.css">
    <!-- Mobile-specific CSS -->
    <link rel="stylesheet" href="./css/mobile.css">
    
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
            background: #1e293b !important;
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1030;
            transition: all 0.4s ease;
        }

        .navbar.scrolled {
            background: var(--primary-color) !important;
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
            color: white !important;
        }

        .login-btn {
            color: white !important;
            border-color: white !important;
        }

        .navbar.scrolled .login-btn {
            color: white !important;
            border-color: white !important;
        }

        .navbar .menu-toggle span {
            background: white;
        }

        .navbar.scrolled .menu-toggle span {
            background: white;
        }

        .main-content {
            position: relative;
            padding-top: 70px;
            min-height: calc(100vh - 80px);
            background: linear-gradient(135deg, var(--background-light) 0%, #ffffff 100%);
        }

        .title-wrapper {
            text-align: center;
            padding: 0 0 20px;
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
            margin-top: 20px; /* Added to reduce space between title and filters */
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
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            padding: 1rem 0;
        }

        .car-card {
            background: white;
            border-radius: var(--card-radius);
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all var(--transition-speed);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            border: none;
            transform: translateY(0);
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .car-image {
            height: 180px;
            position: relative;
            overflow: hidden;
            border-radius: var(--card-radius) var(--card-radius) 0 0;
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
            padding: 1.2rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: white;
            position: relative;
        }

        .car-brand {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .car-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.8rem;
            position: relative;
            padding-bottom: 0.6rem;
            line-height: 1.3;
        }

        .car-description {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .car-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.8rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            padding: 0.8rem;
            border-radius: 10px;
        }

        .info-item {
            display: flex;
            align-items: center;
            color: var(--text-dark);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .info-item i {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            font-size: 0.75rem;
        }

        .car-price {
            padding: 0.8rem;
            border-radius: 10px;
            margin: 0.5rem 0 0.8rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(52, 152, 219, 0.08);
            flex-wrap: wrap;
            border-left: 3px solid var(--secondary-color);
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
            font-weight: 800;
            line-height: 1;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 3px;
        }

        .discount-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 3px 8px;
            font-size: 0.7rem;
            font-weight: 700;
            border-radius: 30px;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(231, 76, 60, 0.3);
            animation: pulse 2s infinite;
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
            font-weight: 800;
            color: #2c3e50;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .book-now-btn {
            width: 100%;
            padding: 0.8rem;
            text-align: center;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
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
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 5px;
            padding-bottom: 5px;
        }

        .main-title {
            font-size: 3rem; /* Reduce the size */
            font-weight: 700;
            margin: 10px 0; /* Adjust spacing */
            padding: 0 10px;
            line-height: 1.2; /* Improve readability */
            text-align: center; /* Center the text */
            /* change the position of title text */
            position: relative;
            z-index: 1;
            display: inline-block;
            padding-bottom: 10px;
            
            /* Background gradient (if needed) */
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
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

        .title-wrap {
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .car-icon-badge {
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2980b9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.1;
        }

        .car-icon-badge i {
            font-size: 3rem;
            color: white;
        }

        .subtitle {
            color: var(--secondary-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .subtitle:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 2px;
            background: var (--secondary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .title-wrap:hover .subtitle:after {
            transform: scaleX(1);
        }

        .page-title h1 {
            color: var(--text-dark);
            margin: 1rem 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .lead.text-muted {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .available-cars-title {
            font-size: 2.8rem; /* Slightly reduced from 4rem */
            font-weight: 800;
            letter-spacing: 2px;
            color: #3498db;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 20px; /* Reduced from 30px */
        }

        .title-separator {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #3498db 0%, #2c3e50 100%);
            margin: 5px auto 25px;
            border-radius: 2px;
        }

        .title-container {
            text-align: center;
            margin: 20px 0 40px; /* Reduced from 60px 0 70px */
            position: relative;
            padding: 20px 0; /* Reduced from 30px */
        }

        .title-accent {
            position: absolute;
            width: 120px;
            height: 120px;
            background: #3498db;
            opacity: 0.1;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
        }

        .available-cars-title {
            font-size: 2.8rem; /* Slightly reduced from 4rem */
            font-weight: 800;
            letter-spacing: 2px;
            color: #2c3e50;
            text-transform: uppercase;
            margin: 0 0 10px; /* Reduced from 15px */
            display: inline-block;
            position: relative;
            text-shadow: 2px 2px 0 rgba(52, 152, 219, 0.3);
            background-image: linear-gradient(135deg, #2c3e50, #3498db);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        .title-separator {
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2c3e50);
            margin: 15px auto 25px;
            border-radius: 2px;
            position: relative;
            overflow: hidden;
        }

        .title-separator:after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.5);
            animation: shine 2s infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .section-subtitle {
            font-size: 1.25rem;
            color: #7f8c8d;
            margin: 0 auto 30px;
            max-width: 600px;
            font-weight: 400;
        }

        .title-icon-left, 
        .title-icon-right {
            position: absolute;
            font-size: 3rem;
            color: #3498db;
            opacity: 0.2;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.5s ease;
        }

        .title-icon-left {
            left: 15%;
        }

        .title-icon-right {
            right: 15%;
        }

        .title-container:hover .title-icon-left {
            transform: translateY(-50%) translateX(-15px);
            opacity: 0.4;
        }

        .title-container:hover .title-icon-right {
            transform: translateY(-50%) translateX(15px);
            opacity: 0.4;
        }

        .title-separator-shine {
            position: absolute;
            top: 0;
            left: -150%;
            width: 150%;
            height: 100%;
            background: linear-gradient(90deg, 
                rgba(255,255,255,0) 0%, 
                rgba(255,255,255,0.8) 50%, 
                rgba(255,255,255,0) 100%);
            animation: shine-effect 3s infinite;
        }

        @keyframes shine-effect {
            0% { left: -150%; }
            100% { left: 150%; }
        }

        .simple-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            text-align: center;
            padding: 20px 0;
            margin: 0 0 20px 0;
            letter-spacing: 2px;
        }

        .page-header {
            text-align: center;
            margin: 40px 0 60px;
            padding: 60px 40px;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.05) 0%, rgba(44, 62, 80, 0.05) 100%);
            border-radius: 30px;
            position: relative;
            overflow: hidden;
        }

        .container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 0 15px;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        .title-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            margin-bottom: 0;
        }

        .subtitle {
            font-size: 1.1rem;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 4px;
            margin: 0;
            padding: 0;
            font-weight: 500;
            display: block;
        }

        .main-title {
            font-size: clamp(2.rem, 5vw, 4.5rem);
            font-weight: 800;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin: 15px 0;
            line-height: 1.2;
            text-align: center;
            white-space: nowrap;
        }

        .title-separator {
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), transparent);
            margin: 5px 0 20px;
            display: block;
        }

        .header-description {
            font-size: 1.2rem;
            color: #7f8c8d;
            max-width: 800px;
            margin: 0;
            text-align: center;
            line-height: 1.6;
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
                  <a href="index.php" class="nav-link"><?php echo $lang['home']; ?></a>
                </li>
                <li class="nav-item">
                  <a href="about.php" class="nav-link"><?php echo $lang['about']; ?></a>
                </li>
                <li class="nav-item">
                  <a href="book.php" class="nav-link active"><?php echo $lang['cars']; ?></a>
                </li>
                <li class="nav-item">
                  <a href="#contact" class="nav-link"><?php echo $lang['contact']; ?></a>
                </li>
              </ul>
            </div>
          </div>

          <!-- Authentication buttons/profile dropdown -->
          <div class="nav-buttons desktop-auth">
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
                <a href="data/change-language.php?lang=en&redirect=<?php echo urlencode($_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>" class="language-option">
                  <i class="flag-icon fas fa-flag flag-icon-uk"></i> English
                </a>
                <a href="data/change-language.php?lang=fr&redirect=<?php echo urlencode($_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>" class="language-option">
                  <i class="flag-icon fas fa-flag flag-icon-france"></i> Français
                </a>
                <a href="data/change-language.php?lang=ar&redirect=<?php echo urlencode($_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>" class="language-option">
                  <i class="flag-icon fas fa-flag flag-icon-morocco"></i> العربية
                </a>
              </div>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="theme-switch-wrapper">
              <span class="theme-switch-label"><i class="fas fa-sun"></i></span>
              <label class="theme-switch">
                <input type="checkbox" id="themeSwitch">
                <span class="slider round">
                  <i class="fas fa-sun sun-icon icon"></i>
                  <i class="fas fa-moon moon-icon icon"></i>
                </span>
              </label>
            </div>
            
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
              <!-- The only instance of login/signup buttons -->
              <div class="auth-buttons">
                <a href="data/authentication.php?action=login" class="nav-btn login-btn"><?php echo $lang['login']; ?></a>
                <a href="data/authentication.php?action=register" class="nav-btn signup-btn"><?php echo $lang['signup']; ?></a>
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

    <!-- Mobile Nav Menu -->
    <div class="mobile-nav">
      <div style="background-color: #e74c3c; color: white; text-align: center; padding: 10px; margin-bottom: 15px; border-radius: 6px;">
        <strong>UPDATED MOBILE MENU</strong>
      </div>
      <ul class="mobile-nav-list">
        <li class="mobile-nav-item">
          <a href="index.php" class="mobile-nav-link"><?php echo $lang['home']; ?></a>
        </li>
        <li class="mobile-nav-item">
          <a href="about.php" class="mobile-nav-link"><?php echo $lang['about']; ?></a>
        </li>
        <li class="mobile-nav-item">
          <a href="book.php" class="mobile-nav-link active"><?php echo $lang['cars']; ?></a>
        </li>
        <li class="mobile-nav-item">
          <a href="#contact" class="mobile-nav-link"><?php echo $lang['contact']; ?></a>
        </li>
      </ul>
      
      <?php if (isset($_SESSION['firstName'])): ?>
      <!-- Mobile profile section (only when user is logged in) -->
      <div class="mobile-profile">
        <div class="mobile-profile-header">
          <div class="mobile-profile-avatar">
            <img src="./images/profile-pic.png" alt="Profile">
          </div>
          <div class="mobile-profile-info">
            <span class="mobile-profile-name"><?= htmlspecialchars($_SESSION['firstName']); ?></span>
            <?php if ($isAdmin): ?>
            <span class="mobile-profile-role">Admin</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="mobile-profile-menu">
          <a href="./data/my_account.php" class="mobile-profile-menu-item">
            <i class="fas fa-user"></i><?php echo $lang['my_account']; ?>
          </a>
          <?php if ($isAdmin): ?>
          <a href="./admin/admin.php" class="mobile-profile-menu-item">
            <i class="fas fa-cog"></i><?php echo $lang['admin_dashboard']; ?>
          </a>
          <?php endif; ?>
          <a href="./data/logout.php" class="mobile-profile-menu-item">
            <i class="fas fa-sign-out-alt"></i><?php echo $lang['logout']; ?>
          </a>
        </div>
      </div>
      <?php else: ?>
      <!-- Mobile auth buttons (only shown in mobile menu) -->
      <div class="mobile-auth">
        <a href="data/authentication.php?action=login" class="nav-btn login-btn"><?php echo $lang['login']; ?></a>
        <a href="data/authentication.php?action=register" class="nav-btn signup-btn"><?php echo $lang['signup']; ?></a>
      </div>
      <?php endif; ?>
      
      <!-- Mobile language selector -->
      <div class="mobile-language-selector">
        <div class="language-options">
          <a href="data/change-language.php?lang=en&redirect=<?php echo urlencode($_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>" class="language-option">
            <i class="flag-icon fas fa-flag flag-icon-uk"></i> English
          </a>
          <a href="data/change-language.php?lang=fr&redirect=<?php echo urlencode($_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>" class="language-option">
            <i class="flag-icon fas fa-flag flag-icon-france"></i> Français
          </a>
          <a href="data/change-language.php?lang=ar&redirect=<?php echo urlencode($_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>" class="language-option">
            <i class="flag-icon fas fa-flag flag-icon-morocco"></i> العربية
          </a>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid px-0">
            <div class="container">
                <div class="page-header">
                    <div class="header-wrapper">
                        <div class="header-content">
                            <div class="title-group">
                                <span class="subtitle"><?php echo $lang['welcome_to_our']; ?></span>
                                <h1 class="main-title"><?php echo $lang['premium_car_collection']; ?></h1>
                                <div class="title-separator"></div>
                            </div>
                            <p class="header-description"><?php echo $lang['discover_our_fleet']; ?></p>
                            <div class="header-badges">
                                <span class="badge"><i class="fas fa-star"></i> <?php echo $lang['premium_selection']; ?></span>
                                <span class="badge"><i class="fas fa-shield-alt"></i> <?php echo $lang['quality_assured']; ?></span>
                                <span class="badge"><i class="fas fa-clock"></i> <?php echo $lang['support']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <!-- Filters Section -->
            <div class="filters-section">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" class="form-control" placeholder="<?php echo $lang['search_placeholder'] ?? 'Search by name, brand or model...'; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="brandFilter">
                            <option value=""><?php echo $lang['all_brands'] ?? 'All Brands'; ?></option>
                            <?php foreach($brands as $brand): ?>
                                <option value="<?php echo htmlspecialchars($brand); ?>"><?php echo htmlspecialchars($brand); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="sortBy">
                            <option value="name"><?php echo $lang['sort_by_name'] ?? 'Sort by Name'; ?></option>
                            <option value="price-low"><?php echo $lang['price_low_to_high'] ?? 'Price: Low to High'; ?></option>
                            <option value="price-high"><?php echo $lang['price_high_to_low'] ?? 'Price: High to Low'; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Cars Grid -->
            <div id="car-listings">
                <?php if (empty($cars)): ?>
                <div class="no-cars-found">
                    <i class="fas fa-car-slash"></i>
                    <h3><?php echo $lang['no_cars_available']; ?></h3>
                    <p><?php echo $lang['no_cars_message']; ?></p>
                </div>
                <?php else: ?>
                <?php foreach ($cars as $car): ?>
                <div class="car-card" data-brand="<?php echo htmlspecialchars($car['brand']); ?>">
                    <div class="car-image">
                        <?php if(!empty($car['primary_image']) && file_exists($car['primary_image'])): ?>
                            <img src="<?php echo htmlspecialchars($car['primary_image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
                        <?php else: ?>
                            <div class="car-image-placeholder">
                                <i class="fas fa-car"></i>
                            </div>
                        <?php endif; ?>
                        <?php if ($car['active_rentals'] >= $car['quantity']): ?>
                            <div class="car-status rented">
                                <i class="fas fa-clock"></i> <?php echo $lang['rented']; ?>
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
                                <p class="next-available"><?php echo $lang['next_available_from']; ?>: <?php echo $nextAvailableDate->format('M d, Y'); ?></p>
                                <a href="checkout.php?car_id=<?php echo $car['id']; ?>&preorder=1&available_from=<?php echo $nextAvailableDate->format('Y-m-d'); ?>" 
                                   class="preorder-btn">
                                    <i class="fas fa-clock"></i> <?php echo $lang['preorder']; ?>
                                </a>
                                <small class="preorder-fee"><?php echo $lang['preorder_fee']; ?></small>
                            </div>
                        <?php else: ?>
                            <div class="car-status available">
                                <i class="fas fa-check"></i> <?php echo $lang['available']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="car-details">
                        <!-- <div class="car-brand"><?php echo htmlspecialchars($car['brand']); ?></div> -->
                        <h3 class="car-title"><?php echo htmlspecialchars($car['name']); ?></h3>
                        
                        <!-- Add car description -->
                        <div class="car-description">
                            <?php echo htmlspecialchars($car['description']); ?>
                        </div>
                        
                        <div class="car-info">
                            <div class="info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo $lang['model']; ?>: <?php echo htmlspecialchars($car['model']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-cog"></i>
                                <span><?php echo $lang['transmission']; ?>: <?php echo $lang['transmission_' . strtolower(str_replace('Automatic', 'auto', $car['transmission']))]; ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-chair"></i>
                                <span><?php echo $lang['interior']; ?>: <?php echo $lang['interior_' . strtolower($car['interior'])]; ?></span>
                            </div>
                            <?php if (!empty($car['fuel_type'])): ?>
                            <div class="info-item">
                                <i class="fas fa-gas-pump"></i>
                                <span><?php echo $lang['fuel_type']; ?>: <?php echo $lang['fuel_' . strtolower($car['fuel_type'])]; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($car['seating_capacity'])): ?>
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span><?php echo $lang['seating_capacity']; ?>: <?php echo htmlspecialchars($car['seating_capacity']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($car['engine_type'])): ?>
                            <div class="info-item">
                                <i class="fas fa-cogs"></i>
                                <span><?php echo $lang['engine_type']; ?>: <?php echo htmlspecialchars($car['engine_type']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="car-price">
                            <?php if (isset($car['discount_display'])): ?>
                                <div class="price-content" data-original-price="<?php echo number_format($car['price'] * $currency_rate, 2); ?>">
                                    <div class="discounted-price">
                                        <?php echo $currency_symbol . number_format($car['discounted_price'] * $currency_rate, 2); ?>
                                        <small>/day</small>
                                        <span class="discount-badge"><?php echo str_replace(' OFF', '', $car['discount_display']); ?></span>
                                    </div>
                                    <div class="original-price">was <?php echo $currency_symbol . number_format($car['price'] * $currency_rate, 2); ?></div>
                                    <?php if (isset($car['discount_end'])): ?>
                                    <div class="discount-countdown" data-end="<?php echo $car['discount_end']; ?>">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> <?php echo $lang['offer_ends_in']; ?>: <span class="countdown-text">Loading...</span>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="price-content">
                                    <div class="regular-price">
                                        <?php echo $currency_symbol . number_format($car['price'] * $currency_rate, 2); ?><small>/day</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!$car['active_rentals'] >= $car['quantity']): ?>
                            <?php if (isset($_SESSION['firstName'])): ?>
                                <a href="checkout.php?car_id=<?php echo $car['id']; ?><?php echo isset($car['discounted_price']) ? '&price=' . ($car['discounted_price'] * $currency_rate) : ''; ?>" class="book-now-btn"><?php echo $lang['book_now']; ?></a>
                            <?php else: ?>
                                <a href="data/authentication.php?action=login" class="book-now-btn"><?php echo $lang['login_to_book']; ?></a>
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
                                <p class="next-available"><?php echo $lang['next_available_from']; ?>: <?php echo $nextAvailableDate->format('M d, Y'); ?></p>
                                <a href="checkout.php?car_id=<?php echo $car['id']; ?>&preorder=1&available_from=<?php echo $nextAvailableDate->format('Y-m-d'); ?>" 
                                   class="preorder-btn">
                                    <i class="fas fa-clock"></i> <?php echo $lang['preorder']; ?>
                                </a>
                                <small class="preorder-fee"><?php echo $lang['preorder_fee']; ?></small>
                            </div>
                            <div class="availability-badge unavailable">
                                <i class="fas fa-times-circle"></i> <?php echo $lang['currently_unavailable']; ?>
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
              <p class="mt-3"><?php echo $lang['footer_description']; ?></p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
              </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
              <h5><?php echo $lang['quick_links']; ?></h5>
              <ul class="footer-links">
                <li><a href="index.php"><?php echo $lang['home']; ?></a></li>
                <li><a href="./book.php"><?php echo $lang['book_now']; ?></a></li>
                <li><a href="about.php"><?php echo $lang['about_us']; ?></a></li>
                <li><a href="#contact"><?php echo $lang['contact_us']; ?></a></li>
              </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
              <h5><?php echo $lang['contact_info']; ?></h5>
              <ul class="contact-info">
                <li><i class="fas fa-map-marker-alt"></i> <?php echo $lang['address']; ?></li>
                <li><i class="fas fa-phone"></i> <?php echo $lang['phone']; ?></li>
                <li><i class="fas fa-envelope"></i> <?php echo $lang['email']; ?></li>
              </ul>
            </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
              <h5><?php echo $lang['team']; ?></h5>
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
              <p class="mb-0">&copy;<span id="currentYear"></span> <span>CARS</span>RENT - <?php echo $lang['all_rights_reserved']; ?></p>
            </div>
            <div class="col-md-6 text-md-end">
              <p class="mb-0"><?php echo $lang['made_by']; ?>: HATTAN OUSSAMA</p>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- Mobile-specific JS -->
    <script src="js/mobile.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dark mode toggle functionality
            const themeSwitch = document.getElementById('themeSwitch');
            
            // Check for saved theme preference or use device preference
            const currentTheme = localStorage.getItem('theme') || 
                                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            
            // Apply the saved theme on page load
            if (currentTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                themeSwitch.checked = true;
            }
            
            // Handle theme switch toggle
            themeSwitch.addEventListener('change', function() {
                if (this.checked) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                }
            });

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
            const mobileNav = document.querySelector('.mobile-nav');
            const body = document.body;
            
            if (menuToggle && mobileNav) {
              menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                menuToggle.classList.toggle('active');
                mobileNav.classList.toggle('active');
                body.classList.toggle('menu-open');
              });
              
              // Close menu when clicking outside
              document.addEventListener('click', function(e) {
                if (!menuToggle.contains(e.target) && !mobileNav.contains(e.target)) {
                  menuToggle.classList.remove('active');
                  mobileNav.classList.remove('active');
                  body.classList.remove('menu-open');
                }
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
            
            // ...existing code...
        });
    </script>
</body>
</html>
<?php
// Move connection close to the end of the file
$conn->close();
?>
