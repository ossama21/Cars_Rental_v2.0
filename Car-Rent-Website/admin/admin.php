<?php 
session_start(); 
include '../data/connect.php';
include '../data/auth.php';

// Language selection handling
$availableLangs = ['en', 'fr', 'ar'];
$lang_code = isset($_SESSION['lang']) && in_array($_SESSION['lang'], $availableLangs) ? $_SESSION['lang'] : 'en';

// Set html direction for Arabic
$dir = $lang_code === 'ar' ? 'rtl' : 'ltr';

// Include the selected language file
include_once "../languages/{$lang_code}.php";

checkAdminAccess();

$email = $_SESSION['email'];
// Updated query to use firstName and lastName instead of name
$sql = "SELECT role, firstName, lastName FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Create a display name from firstName and lastName
$displayName = ($user) ? $user['firstName'] . ' ' . $user['lastName'] : $email;

// Get statistics for dashboard
// Total users
$sqlUsers = "SELECT COUNT(*) as total FROM users";
$totalUsers = $conn->query($sqlUsers)->fetch_assoc()['total'];

// Total cars
$sqlCars = "SELECT COUNT(*) as total FROM cars";
$totalCars = $conn->query($sqlCars)->fetch_assoc()['total'];

// Total rentals - Using services table instead of bookings
$sqlRentals = "SELECT COUNT(*) as total FROM services";
$rentalResult = $conn->query($sqlRentals);
$totalRentals = $rentalResult ? $rentalResult->fetch_assoc()['total'] : 0;

// Revenue - From services table
$sqlRevenue = "SELECT SUM(amount) as total FROM services";
$revenueResult = $conn->query($sqlRevenue);
$totalRevenue = $revenueResult && $revenueResult->num_rows > 0 ? $revenueResult->fetch_assoc()['total'] : 0;

// Recent activities - Using services table
$sqlActivities = "SELECT s.id, 
                    s.username as user_name, 
                    c.name as car_name, 
                    s.start_date as pickup_date, 
                    s.created_at as booking_date,
                    'completed' as status
                FROM services s 
                JOIN cars c ON s.car_id = c.id 
                ORDER BY s.created_at DESC LIMIT 5";
$activities = $conn->query($sqlActivities);

// Recent cars added
// Check if price_per_day column exists, else use price
$columnsResult = $conn->query("SHOW COLUMNS FROM cars LIKE 'price_per_day'");
$priceColumn = ($columnsResult && $columnsResult->num_rows > 0) ? 'price_per_day' : 'price';

// Check if added_date column exists, else use current_timestamp
$columnsResult = $conn->query("SHOW COLUMNS FROM cars LIKE 'added_date'");
$dateColumn = ($columnsResult && $columnsResult->num_rows > 0) ? 'added_date' : 'CURRENT_TIMESTAMP as added_date';

$sqlRecentCars = "SELECT id, name, image, $priceColumn as price_per_day, $dateColumn 
                  FROM cars ORDER BY id DESC LIMIT 5";
$recentCars = $conn->query($sqlRecentCars);

// Get monthly revenue for chart - Using services table
$sqlMonthlyRevenue = "SELECT 
                        MONTH(created_at) as month,
                        YEAR(created_at) as year,
                        SUM(amount) as total
                      FROM services
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                      GROUP BY YEAR(created_at), MONTH(created_at)
                      ORDER BY year, month";
$monthlyRevenue = $conn->query($sqlMonthlyRevenue);

$months = [];
$revenues = [];

if ($monthlyRevenue && $monthlyRevenue->num_rows > 0) {
    while ($row = $monthlyRevenue->fetch_assoc()) {
        $monthName = date('M', mktime(0, 0, 0, $row['month'], 10));
        $months[] = $monthName;
        $revenues[] = $row['total'];
    }
}

// Get car categories distribution
// Check if category column exists, else use brand
$columnsResult = $conn->query("SHOW COLUMNS FROM cars LIKE 'category'");
$categoryColumn = ($columnsResult && $columnsResult->num_rows > 0) ? 'category' : 'brand';

$sqlCarCategories = "SELECT $categoryColumn as category, COUNT(*) as total FROM cars GROUP BY $categoryColumn";
$carCategories = $conn->query($sqlCarCategories);

$categories = [];
$categoryCounts = [];

if ($carCategories && $carCategories->num_rows > 0) {
    while ($row = $carCategories->fetch_assoc()) {
        $categoryName = !empty($row['category']) ? $row['category'] : 'Other';
        $categories[] = $categoryName;
        $categoryCounts[] = $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Rental System</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../css/modern.css">
    <link rel="stylesheet" href="../css/language-selector.css">
    
    <!-- Inline Admin Styles (as backup if external CSS fails) -->
    <style>
        /* Admin Dashboard Core Styles */
        :root {
          --admin-primary: #2c5282;
          --admin-secondary: #4299e1;
          --admin-accent: #f6ad55;
          --admin-bg: #f7fafc;
          --admin-dark: #1a202c;
          --admin-success: #48bb78;
          --admin-warning: #ed8936;
          --admin-danger: #e53e3e;
          --admin-gray-light: #e2e8f0;
          --admin-card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
          --admin-transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--admin-bg);
            margin: 0;
            padding: 0;
        }
        
        /* Admin Layout */
        .admin-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background-color: var(--admin-dark);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            overflow-y: auto;
            transition: var(--admin-transition);
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-collapsed .admin-sidebar {
            left: -280px;
        }
        
        .admin-sidebar-header {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .admin-logo i {
            margin-right: 0.75rem;
            color: var(--admin-accent);
        }
        
        .admin-sidebar-menu {
            padding: 1.5rem 0;
        }
        
        .sidebar-menu-item {
            position: relative;
        }
        
        .sidebar-menu-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--admin-transition);
        }
        
        .sidebar-menu-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            transition: var(--admin-transition);
        }
        
        .sidebar-menu-link span {
            transition: var(--admin-transition);
        }
        
        .sidebar-menu-link:hover, 
        .sidebar-menu-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu-link:hover i, 
        .sidebar-menu-link.active i {
            color: var(--admin-accent);
        }
        
        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: rgba(0, 0, 0, 0.15);
        }
        
        .sidebar-submenu.active {
            max-height: 500px;
        }
        
        .submenu-item a {
            padding: 0.5rem 1.5rem 0.5rem 3.5rem;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            display: block;
            transition: var(--admin-transition);
        }
        
        .submenu-item a:hover,
        .submenu-item a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .has-submenu::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .has-submenu.active::after {
            transform: rotate(180deg);
        }
        
        .admin-main {
            grid-column: 2;
            padding: 2rem;
            transition: var(--admin-transition);
        }
        
        .sidebar-collapsed .admin-main {
            grid-column: 1 / span 2;
            margin-left: 0;
        }
        
        .admin-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            color: var(--admin-dark);
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
        }
        
        .admin-user-info {
            margin-right: 1rem;
            text-align: right;
        }
        
        .admin-user-name {
            font-weight: 600;
        }
        
        .admin-user-role {
            font-size: 0.875rem;
            color: var(--admin-secondary);
        }
        
        .admin-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .admin-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Admin Stats Grid */
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .admin-stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--admin-card-shadow);
            transition: var(--admin-transition);
        }
        
        .admin-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .admin-stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .admin-stat-title {
            font-size: 1rem;
            color: var(--admin-dark);
            font-weight: 500;
        }
        
        .admin-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .admin-stat-icon.users {
            background-color: rgba(66, 153, 225, 0.1);
            color: var(--admin-secondary);
        }
        
        .admin-stat-icon.cars {
            background-color: rgba(246, 173, 85, 0.1);
            color: var(--admin-accent);
        }
        
        .admin-stat-icon.revenue {
            background-color: rgba(72, 187, 120, 0.1);
            color: var(--admin-success);
        }
        
        .admin-stat-icon.rentals {
            background-color: rgba(237, 137, 54, 0.1);
            color: var(--admin-warning);
        }
        
        .admin-stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .admin-stat-change {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        
        .admin-stat-change.positive {
            color: var(--admin-success);
        }
        
        .admin-stat-change.negative {
            color: var(--admin-danger);
        }
        
        .admin-stat-change i {
            margin-right: 0.25rem;
        }
        
        /* Admin Charts & Content Cards */
        .admin-charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .admin-chart-card,
        .admin-card,
        .admin-table-card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--admin-card-shadow);
            height: 100%;
        }
        
        .admin-chart-header,
        .admin-card-header,
        .admin-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .admin-chart-title,
        .admin-card-title,
        .admin-table-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .admin-chart-container {
            height: 300px;
            position: relative;
        }
        
        .admin-content-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .activity-list,
        .task-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .activity-item,
        .task-item {
            display: flex;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--admin-gray-light);
        }
        
        .activity-item:last-child,
        .task-item:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .activity-icon.success {
            background-color: rgba(72, 187, 120, 0.1);
            color: var(--admin-success);
        }
        
        .activity-content,
        .task-content {
            flex: 1;
        }
        
        .activity-title,
        .task-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--admin-dark);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table thead th {
            padding: 1rem;
            border-bottom: 2px solid var(--admin-gray-light);
            text-align: left;
            font-weight: 600;
            color: var(--admin-dark);
        }
        
        .admin-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--admin-gray-light);
            vertical-align: middle;
        }
        
        .admin-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--admin-transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            text-decoration: none;
        }
        
        .admin-btn i {
            margin-right: 0.5rem;
        }
        
        .admin-btn-primary {
            background-color: var(--admin-secondary);
            color: white;
        }
        
        .admin-btn-secondary {
            background-color: var(--admin-gray-light);
            color: var(--admin-dark);
        }
        
        .admin-btn-danger {
            background-color: var(--admin-danger);
            color: white;
        }
        
        .admin-btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .admin-charts-row {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 991px) {
            .admin-wrapper {
                grid-template-columns: 1fr;
            }
            
            .admin-sidebar {
                left: -280px;
            }
            
            .admin-sidebar.show {
                left: 0;
            }
            
            .admin-main {
                grid-column: 1;
                margin-left: 0;
            }
        }
        
        @media (max-width: 768px) {
            .admin-content-row {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .admin-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-user-info {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper" id="adminWrapper">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <a href="admin.php" class="admin-logo">
                    <i class="fas fa-car"></i>
                    <span>CarRental</span> Admin
                </a>
            </div>
            
            <nav class="admin-sidebar-menu">
                <div class="sidebar-menu-item">
                    <a href="admin.php" class="sidebar-menu-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_cars.php" class="sidebar-menu-link">
                        <i class="fas fa-car"></i>
                        <span>Manage Cars</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_users.php" class="sidebar-menu-link">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_payments.php" class="sidebar-menu-link">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Manage Payments</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link has-submenu" id="bookingsMenu">
                        <i class="fas fa-calendar-check"></i>
                        <span>Bookings</span>
                    </a>
                    <div class="sidebar-submenu" id="bookingsSubmenu">
                        <div class="submenu-item">
                            <a href="#">All Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Pending Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Confirmed Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Completed Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Cancelled Bookings</a>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link has-submenu" id="reportsMenu">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                    <div class="sidebar-submenu" id="reportsSubmenu">
                        <div class="submenu-item">
                            <a href="#">Revenue Reports</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Booking Reports</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">User Reports</a>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item mt-4">
                    <a href="../data/logout.php" class="sidebar-menu-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item mt-2">
                    <a href="../index.php" class="sidebar-menu-link">
                        <i class="fas fa-home"></i>
                        <span>Back to Home</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    <!-- Language Selector -->
                    <div class="language-selector me-3">
                        <a href="#" class="current-lang">
                            <?php if($lang_code == 'en'): ?>
                                <i class="fas fa-flag flag-icon-uk"></i> English
                            <?php elseif($lang_code == 'fr'): ?>
                                <i class="fas fa-flag flag-icon-france"></i> Français
                            <?php elseif($lang_code == 'ar'): ?>
                                <i class="fas fa-flag flag-icon-morocco"></i> العربية
                            <?php endif; ?>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="language-dropdown">
                            <a href="../data/change-language.php?lang=en&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'en' ? 'active' : ''; ?>">
                                <i class="fas fa-flag flag-icon-uk"></i> English
                            </a>
                            <a href="../data/change-language.php?lang=fr&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'fr' ? 'active' : ''; ?>">
                                <i class="fas fa-flag flag-icon-france"></i> Français
                            </a>
                            <a href="../data/change-language.php?lang=ar&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'ar' ? 'active' : ''; ?>">
                                <i class="fas fa-flag flag-icon-morocco"></i> العربية
                            </a>
                        </div>
                    </div>
                    
                    <div class="admin-user">
                        <div class="admin-user-info">
                            <div class="admin-user-name"><?php echo htmlspecialchars($displayName); ?></div>
                            <div class="admin-user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                        </div>
                        <div class="admin-user-avatar">
                            <img src="../images/profile-pic.png" alt="Admin">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-dashboard">
                <!-- Welcome & Overview -->
                <h1>Dashboard</h1>
                <p class="text-muted mb-4">Welcome back, <?php echo htmlspecialchars($displayName); ?>. Here's what's happening with your car rental service today.</p>
                
                <!-- Stats Grid -->
                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Total Users</div>
                            <div class="admin-stat-icon users">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($totalUsers); ?></div>
                        <div class="admin-stat-change positive">
                            <i class="fas fa-arrow-up"></i> 12% since last month
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Cars Available</div>
                            <div class="admin-stat-icon cars">
                                <i class="fas fa-car"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($totalCars); ?></div>
                        <div class="admin-stat-change positive">
                            <i class="fas fa-arrow-up"></i> 7% since last month
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Total Rentals</div>
                            <div class="admin-stat-icon rentals">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($totalRentals); ?></div>
                        <div class="admin-stat-change positive">
                            <i class="fas fa-arrow-up"></i> 18% since last month
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Total Revenue</div>
                            <div class="admin-stat-icon revenue">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
                        <div class="admin-stat-change positive">
                            <i class="fas fa-arrow-up"></i> 24% since last month
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="admin-charts-row">
                    <div class="admin-chart-card">
                        <div class="admin-chart-header">
                            <div class="admin-chart-title">Revenue Overview</div>
                            <div class="admin-chart-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #4299e1;"></div>
                                    <span>Monthly Revenue</span>
                                </div>
                            </div>
                        </div>
                        <div class="admin-chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="admin-chart-card">
                        <div class="admin-chart-header">
                            <div class="admin-chart-title">Car Categories</div>
                        </div>
                        <div class="admin-chart-container">
                            <canvas id="categoriesChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Content Row -->
                <div class="admin-content-row">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="admin-card-title">Recent Activities</div>
                            <a href="#" class="admin-card-action">View All</a>
                        </div>
                        
                        <div class="activity-list">
                            <?php if($activities && $activities->num_rows > 0): ?>
                                <?php while($activity = $activities->fetch_assoc()): ?>
                                    <div class="activity-item">
                                        <?php 
                                        $iconClass = 'activity-icon success'; // For services, they're all completed
                                        $icon = 'fa-check-circle';
                                        ?>
                                        <div class="<?php echo $iconClass; ?>">
                                            <i class="fas <?php echo $icon; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title">
                                                <?php echo htmlspecialchars($activity['user_name']); ?> rented 
                                                <?php echo htmlspecialchars($activity['car_name']); ?>
                                            </div>
                                            <div class="activity-time">
                                                <?php 
                                                    $bookingDate = new DateTime($activity['booking_date']);
                                                    echo $bookingDate->format('M d, Y - h:i A'); 
                                                ?> · 
                                                <span class="badge bg-success">
                                                    Completed
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No recent activities</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="admin-card-title">Tasks</div>
                            <a href="#" class="admin-card-action">Add New</a>
                        </div>
                        
                        <div class="task-list">
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input type="checkbox" id="task1">
                                </div>
                                <div class="task-content">
                                    <div class="task-title">Review pending car bookings</div>
                                    <div class="task-date">Today</div>
                                </div>
                                <div class="task-priority high">High</div>
                            </div>
                            
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input type="checkbox" id="task2">
                                </div>
                                <div class="task-content">
                                    <div class="task-title">Update car availability</div>
                                    <div class="task-date">Today</div>
                                </div>
                                <div class="task-priority medium">Medium</div>
                            </div>
                            
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input type="checkbox" id="task3" checked>
                                </div>
                                <div class="task-content">
                                    <div class="task-title">Send invoice to client</div>
                                    <div class="task-date">Yesterday</div>
                                </div>
                                <div class="task-priority medium">Medium</div>
                            </div>
                            
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input type="checkbox" id="task4">
                                </div>
                                <div class="task-content">
                                    <div class="task-title">Check maintenance status of cars</div>
                                    <div class="task-date">Tomorrow</div>
                                </div>
                                <div class="task-priority low">Low</div>
                            </div>
                            
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input type="checkbox" id="task5">
                                </div>
                                <div class="task-content">
                                    <div class="task-title">Prepare monthly revenue report</div>
                                    <div class="task-date">Aug 27, 2023</div>
                                </div>
                                <div class="task-priority high">High</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Cars Table -->
                <div class="admin-table-card">
                    <div class="admin-table-header">
                        <div class="admin-table-title">Recently Added Cars</div>
                        <div class="admin-table-actions">
                            <a href="add_car.php" class="admin-btn admin-btn-primary admin-btn-sm">
                                <i class="fas fa-plus"></i> Add New Car
                            </a>
                        </div>
                    </div>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price/Day</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recentCars && $recentCars->num_rows > 0): ?>
                                <?php while($car = $recentCars->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" style="width: 50px; height: 40px; object-fit: contain;">
                                        </td>
                                        <td><?php echo htmlspecialchars($car['id']); ?></td>
                                        <td><?php echo htmlspecialchars($car['name']); ?></td>
                                        <td>$<?php echo htmlspecialchars($car['price_per_day']); ?>/day</td>
                                        <td>
                                            <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="admin-btn admin-btn-secondary admin-btn-sm me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_car.php?id=<?php echo $car['id']; ?>" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('Are you sure you want to delete this car?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No cars found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div class="admin-table-pagination">
                        <div class="pagination-info">
                            Showing 5 out of <?php echo $totalCars; ?> cars
                        </div>
                        <div class="pagination-controls">
                            <a href="manage_cars.php" class="admin-btn admin-btn-secondary admin-btn-sm">
                                View All Cars
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle sidebar
        const toggleSidebar = document.getElementById('toggleSidebar');
        const adminWrapper = document.getElementById('adminWrapper');
        const adminSidebar = document.getElementById('adminSidebar');
        
        toggleSidebar.addEventListener('click', function() {
            adminWrapper.classList.toggle('sidebar-collapsed');
            if (window.innerWidth < 992) {
                adminSidebar.classList.toggle('show');
            }
        });
        
        // Handle submenu toggles
        const submenuToggles = document.querySelectorAll('.has-submenu');
        
        submenuToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                
                const targetId = this.getAttribute('id');
                const submenu = document.getElementById(targetId.replace('Menu', 'Submenu'));
                
                if (submenu) {
                    submenu.classList.toggle('active');
                }
            });
        });
        
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
        
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Revenue',
                    backgroundColor: 'rgba(66, 153, 225, 0.2)',
                    borderColor: '#4299e1',
                    pointBackgroundColor: '#4299e1',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#4299e1',
                    data: <?php echo json_encode($revenues); ?>,
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4],
                            color: '#e2e8f0'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Categories Chart
        const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
        const categoriesChart = new Chart(categoriesCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    data: <?php echo json_encode($categoryCounts); ?>,
                    backgroundColor: [
                        '#4299e1', // blue
                        '#f6ad55', // orange
                        '#48bb78', // green
                        '#ed8936', // dark orange
                        '#9f7aea', // purple
                        '#f56565'  // red
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>