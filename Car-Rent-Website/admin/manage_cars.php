<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Use the auth function to check admin access
checkAdminAccess();

$email = $_SESSION['email'];
// Get admin info for display
$sql = "SELECT role, firstName, lastName FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Create a display name from firstName and lastName
$displayName = ($user) ? $user['firstName'] . ' ' . $user['lastName'] : $email;

// Handle search and filtering
$search = isset($_GET['search']) ? $_GET['search'] : '';
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$transmission = isset($_GET['transmission']) ? $_GET['transmission'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query with filters
$sqlCars = "SELECT c.*, 
    CASE 
        WHEN d.discount_type = 'percentage' THEN CONCAT(d.discount_value, '%')
        WHEN d.discount_type = 'fixed' THEN CONCAT('$', d.discount_value)
        ELSE NULL 
    END as discount_display,
    d.discount_type,
    d.discount_value,
    d.start_date as discount_start,
    d.end_date as discount_end,
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
$params = [];
$types = "";

if (!empty($search)) {
    $sqlCars .= " AND (name LIKE ? OR model LIKE ? OR brand LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
}

if (!empty($brand)) {
    $sqlCars .= " AND brand = ?";
    $params[] = $brand;
    $types .= "s";
}

if (!empty($transmission)) {
    $sqlCars .= " AND transmission = ?";
    $params[] = $transmission;
    $types .= "s";
}

// We'll handle the status filter differently since 'available' column doesn't exist
// We'll fetch all cars and filter by availability status in PHP instead

// Add sorting
$sqlCars .= " ORDER BY id DESC";

$stmt = $conn->prepare($sqlCars);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$carsResult = $stmt->get_result();

// Create car_discounts table if it doesn't exist
$createTableSQL = file_get_contents(__DIR__ . '/car_discounts.sql');
$conn->multi_query($createTableSQL);
while ($conn->more_results()) {
    $conn->next_result();
}

// Get car brands for filter dropdown
$sqlBrands = "SELECT DISTINCT brand FROM cars ORDER BY brand";
$brandsResult = $conn->query($sqlBrands);
$brands = [];
while ($brandRow = $brandsResult->fetch_assoc()) {
    $brands[] = $brandRow['brand'];
}

// Get total car count
$sqlTotal = "SELECT COUNT(*) as total FROM cars";
$totalCars = $conn->query($sqlTotal)->fetch_assoc()['total'];

// Instead of querying directly for available cars, we'll determine this from the services table
// Get currently booked cars (simplified implementation)
$currentDate = date('Y-m-d');
$sqlRented = "SELECT DISTINCT car_id FROM services WHERE ? BETWEEN DATE(start_date) AND DATE(end_date)";
$rentedStmt = $conn->prepare($sqlRented);
$rentedStmt->bind_param("s", $currentDate);
$rentedStmt->execute();
$rentedResult = $rentedStmt->get_result();

// Get reserved cars (future bookings that haven't started yet)
$sqlReserved = "SELECT DISTINCT car_id FROM services WHERE DATE(start_date) > ?";
$reservedStmt = $conn->prepare($sqlReserved);
$reservedStmt->bind_param("s", $currentDate);
$reservedStmt->execute();
$reservedResult = $reservedStmt->get_result();

// Create arrays of car IDs for each status
$rentedCarIds = [];
$reservedCarIds = [];
while ($row = $rentedResult->fetch_assoc()) {
    $rentedCarIds[] = $row['car_id'];
}
while ($row = $reservedResult->fetch_assoc()) {
    $reservedCarIds[] = $row['car_id'];
}

// Count cars by status
$rentedCars = count($rentedCarIds);
$reservedCars = count($reservedCarIds);
$availableCars = $totalCars - ($rentedCars + $reservedCars);

// If status filter is applied, filter the results in PHP
if (!empty($status)) {
    $filteredCarsResult = [];
    while ($car = $carsResult->fetch_assoc()) {
        $isRented = in_array($car['id'], $rentedCarIds);
        
        if (($status === 'available' && !$isRented) || 
            ($status === 'rented' && $isRented)) {
            $filteredCarsResult[] = $car;
        }
    }
    
    // Reset the result set
    $carsResult->free();
    $carsResult = new mysqli_result();
    
    // Add the filtered cars back to the result
    foreach ($filteredCarsResult as $car) {
        $carsResult->data_seek[] = $car;
    }
} else {
    // If no status filter, we keep all cars in the result
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Admin Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles - Using absolute path to ensure the CSS is found -->
    <link rel="stylesheet" href="../css/modern.css">
    
    <!-- Adding inline CSS as a fallback to ensure basic styling -->
    <style>
        /* Basic styling for admin dashboard */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 270px;
            background-color: #2d3748;
            color: #fff;
            transition: all 0.3s;
            position: fixed;
            height: 100%;
            z-index: 1000;
        }
        
        .admin-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .admin-logo {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu-item {
            margin-bottom: 5px;
        }
        
        .sidebar-menu-link {
            padding: 10px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        
        .sidebar-menu-link:hover, .sidebar-menu-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar-submenu {
            padding-left: 30px;
            display: none;
        }
        
        .sidebar-submenu.active {
            display: block;
        }
        
        .admin-main {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .admin-topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 280px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 0.75rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.01);
            display: flex;
            
            align-items: center;
            z-index: 999;
            height: 60px;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-user-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .admin-content {
            padding: 30px;
        }
        
        .admin-content-header {
            margin-bottom: 30px;
        }
        
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .admin-stat-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .admin-stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .admin-stat-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(66, 153, 225, 0.1);
            color: #4299e1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.5rem;
        }
        
        .admin-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .admin-table-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .admin-table-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-table {
            width: 100%;
        }
        
        .admin-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .admin-table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .admin-btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            border: none;
        }
        
        .admin-btn-primary {
            background-color: #4299e1;
            color: #fff;
        }
        
        .admin-btn-secondary {
            background-color: #e2e8f0;
            color: #4a5568;
        }
        
        .admin-btn-danger {
            background-color: #e53e3e;
            color: #fff;
        }
        
        .admin-btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
        }
        
        .filter-row {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .admin-table-pagination {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .pagination-button {
            width: 35px;
            height: 35px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            background-color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 2px;
            transition: all 0.2s;
        }
        
        .pagination-button.active {
            background-color: #4299e1;
            color: #fff;
            border-color: #4299e1;
        }
        
        @media (max-width: 992px) {
            .admin-sidebar {
                left: -250px;
            }
            
            .admin-sidebar.show {
                left: 0;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .sidebar-collapsed .admin-sidebar {
                left: -250px;
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
                    <a href="admin.php" class="sidebar-menu-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_cars.php" class="sidebar-menu-link active">
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
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
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
            
            <div class="admin-content">
                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Page Header -->
                <div class="admin-content-header">
                    <h1><i class="fas fa-car me-2"></i>Manage Cars</h1>
                    <p class="text-muted">Manage your fleet of rental cars</p>
                </div>
                
                <!-- Cars Summary Cards -->
                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Total Cars</div>
                            <div class="admin-stat-icon cars">
                                <i class="fas fa-car"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($totalCars); ?></div>
                        <div class="admin-stat-change">
                            <i class="fas fa-info-circle"></i> In fleet
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Available Cars</div>
                            <div class="admin-stat-icon" style="background-color: rgba(72, 187, 120, 0.1); color: #48bb78;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($availableCars); ?></div>
                        <div class="admin-stat-change positive">
                            <i class="fas fa-unlock"></i> Ready to rent
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Rented Cars</div>
                            <div class="admin-stat-icon" style="background-color: rgba(237, 137, 54, 0.1); color: #ed8936;">
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($rentedCars); ?></div>
                        <div class="admin-stat-change">
                            <i class="fas fa-lock"></i> Currently rented
                        </div>
                        <button class="admin-btn admin-btn-secondary mt-2 w-100" data-bs-toggle="modal" data-bs-target="#rentedCarsModal">
                            <i class="fas fa-eye me-2"></i>View Details
                        </button>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Reserved Cars</div>
                            <div class="admin-stat-icon" style="background-color: rgba(66, 153, 225, 0.1); color: #4299e1;">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($reservedCars); ?></div>
                        <div class="admin-stat-change">
                            <i class="fas fa-calendar-alt"></i> Future bookings
                        </div>
                        <button class="admin-btn admin-btn-secondary mt-2 w-100" data-bs-toggle="modal" data-bs-target="#reservedCarsModal">
                            <i class="fas fa-eye me-2"></i>View Details
                        </button>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Quick Actions</div>
                            <div class="admin-stat-icon" style="background-color: rgba(66, 153, 225, 0.1); color: #4299e1;">
                                <i class="fas fa-bolt"></i>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-2">
                            <a href="add_car.php" class="admin-btn admin-btn-primary">
                                <i class="fas fa-plus me-2"></i>Add New Car
                            </a>
                            <a href="#" class="admin-btn admin-btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="fas fa-file-import me-2"></i>Import Cars
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Car Management Table -->
                <div class="admin-table-card">
                    <div class="admin-table-header">
                        <div class="admin-table-title">Car Inventory</div>
                        
                        <div class="admin-table-actions d-flex gap-2">
                            <button class="admin-btn admin-btn-primary admin-btn-sm" data-bs-toggle="modal" data-bs-target="#bulkDiscountModal">
                                <i class="fas fa-tags me-1"></i> Bulk Discount
                            </button>
                            <div class="dropdown">
                                <button class="admin-btn admin-btn-secondary admin-btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-export me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                                </ul>
                            </div>
                            
                            <a href="add_car.php" class="admin-btn admin-btn-primary admin-btn-sm">
                                <i class="fas fa-plus me-1"></i> Add New Car
                            </a>
                        </div>
                    </div>
                    
                    <!-- Filter and Search Row -->
                    <div class="filter-row mb-4">
                        <form action="manage_cars.php" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search cars..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="brand" class="form-select">
                                    <option value="">All Brands</option>
                                    <?php foreach($brands as $brandOption): ?>
                                        <option value="<?php echo htmlspecialchars($brandOption); ?>" <?php echo $brand === $brandOption ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brandOption); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="transmission" class="form-select">
                                    <option value="">All Transmissions</option>
                                    <option value="Automatic" <?php echo $transmission === 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                                    <option value="Manual" <?php echo $transmission === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="rented" <?php echo $status === 'rented' ? 'selected' : ''; ?>>Rented</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="admin-btn admin-btn-primary w-100">Filter</button>
                            </div>
                            <?php if(!empty($search) || !empty($brand) || !empty($transmission) || !empty($status)): ?>
                                <div class="col-12">
                                    <a href="manage_cars.php" class="btn btn-link text-decoration-none">
                                        <i class="fas fa-times-circle"></i> Clear filters
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th width="80">Image</th>
                                    <th>Car Details</th>
                                    <th>Specifications</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($carsResult->num_rows > 0): ?>
                                    <?php while($car = $carsResult->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if(!empty($car['image']) && file_exists('../' . $car['image'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" style="width: 70px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                <?php else: ?>
                                                    <div style="width: 70px; height: 50px; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                                        <i class="fas fa-car text-secondary"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($car['name']); ?></div>
                                                <div class="text-muted small">ID: <?php echo htmlspecialchars($car['id']); ?></div>
                                                <div class="text-muted small">Brand: <?php echo htmlspecialchars($car['brand']); ?></div>
                                            </td>
                                            <td>
                                                <div><i class="fas fa-calendar me-2 text-secondary"></i><?php echo htmlspecialchars($car['model']); ?></div>
                                                <div><i class="fas fa-cog me-2 text-secondary"></i><?php echo htmlspecialchars($car['transmission']); ?></div>
                                                <div><i class="fas fa-chair me-2 text-secondary"></i><?php echo htmlspecialchars($car['interior']); ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $isRented = in_array($car['id'], $rentedCarIds);
                                                    $isReserved = in_array($car['id'], $reservedCarIds);
                                                    if ($isRented): 
                                                ?>
                                                    <span class="badge bg-warning text-dark">Rented</span>
                                                <?php elseif ($isReserved): ?>
                                                    <span class="badge bg-info">Reserved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Available</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold">$<?php echo htmlspecialchars($car['price']); ?></div>
                                                <?php if (isset($car['discount_display'])): ?>
                                                    <div class="text-success small">
                                                        <i class="fas fa-tag"></i> <?php echo $car['discount_display']; ?> OFF
                                                    </div>
                                                    <div class="text-muted small text-decoration-line-through">
                                                        Original: $<?php echo $car['price']; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="text-muted small">per day</div>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="admin-btn admin-btn-secondary admin-btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" title="View" data-bs-toggle="modal" data-bs-target="#carModal<?php echo $car['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <!-- Removed tag icon button -->
                                                    <a href="delete_car.php?id=<?php echo $car['id']; ?>" class="admin-btn admin-btn-danger admin-btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this car?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Car Details Modal -->
                                        <div class="modal fade" id="carModal<?php echo $car['id']; ?>" tabindex="-1" aria-labelledby="carModalLabel<?php echo $car['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="carModalLabel<?php echo $car['id']; ?>"><?php echo htmlspecialchars($car['name']); ?> Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-5">
                                                                <?php if(!empty($car['image']) && file_exists('../' . $car['image'])): ?>
                                                                    <img src="../<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" class="img-fluid rounded" style="max-height: 300px; width: 100%; object-fit: cover;">
                                                                <?php else: ?>
                                                                    <div style="height: 300px; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                                                        <i class="fas fa-car fa-3x text-secondary"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <h4><?php echo htmlspecialchars($car['name']); ?></h4>
                                                                <p class="text-muted"><?php echo htmlspecialchars($car['description']); ?></p>
                                                                
                                                                <div class="row mt-4">
                                                                    <div class="col-6">
                                                                        <strong>Brand:</strong> <?php echo htmlspecialchars($car['brand']); ?>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <strong>Model:</strong> <?php echo htmlspecialchars($car['model']); ?>
                                                                    </div>
                                                                    <div class="col-6 mt-2">
                                                                        <strong>Transmission:</strong> <?php echo htmlspecialchars($car['transmission']); ?>
                                                                    </div>
                                                                    <div class="col-6 mt-2">
                                                                        <strong>Interior:</strong> <?php echo htmlspecialchars($car['interior']); ?>
                                                                    </div>
                                                                    <div class="col-6 mt-2">
                                                                        <strong>Price per day:</strong> $<?php echo htmlspecialchars($car['price']); ?>
                                                                    </div>
                                                                    <div class="col-6 mt-2">
                                                                        <strong>Status:</strong> 
                                                                        <?php 
                                                                            $isRented = in_array($car['id'], $rentedCarIds);
                                                                            $isReserved = in_array($car['id'], $reservedCarIds);
                                                                            if ($isRented): 
                                                                        ?>
                                                                            <span class="badge bg-warning text-dark">Rented</span>
                                                                        <?php elseif ($isReserved): ?>
                                                                            <span class="badge bg-info">Reserved</span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-success">Available</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="admin-btn admin-btn-primary">
                                                            <i class="fas fa-edit me-1"></i> Edit Car
                                                        </a>
                                                        <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-car fa-3x text-muted mb-3"></i>
                                                <h5>No Cars Found</h5>
                                                <p class="text-muted">No cars match your search criteria.</p>
                                                <a href="add_car.php" class="admin-btn admin-btn-primary mt-2">
                                                    <i class="fas fa-plus me-2"></i>Add New Car
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="admin-table-pagination">
                        <div class="pagination-info">
                            Showing <?php echo $carsResult->num_rows; ?> out of <?php echo $totalCars; ?> cars
                        </div>
                        <div class="pagination-controls">
                            <!-- Simplified pagination for now -->
                            <!-- In a real app, you would implement full pagination -->
                            <button class="pagination-button" disabled><i class="fas fa-angle-double-left"></i></button>
                            <button class="pagination-button" disabled><i class="fas fa-angle-left"></i></button>
                            <button class="pagination-button active">1</button>
                            <button class="pagination-button" disabled><i class="fas fa-angle-right"></i></button>
                            <button class="pagination-button" disabled><i class="fas fa-angle-double-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Import Cars Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Cars</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Upload CSV or Excel file</label>
                            <input class="form-control" type="file" id="importFile" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            <div class="form-text">File should contain all required car information in the correct format.</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="headerRow" checked>
                            <label class="form-check-label" for="headerRow">File contains header row</label>
                        </div>
                        <button type="button" class="admin-btn admin-btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload and Import
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rented Cars Modal -->
    <div class="modal fade" id="rentedCarsModal" tabindex="-1" aria-labelledby="rentedCarsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rentedCarsModalLabel">Currently Rented Cars</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    // Get currently rented cars with details
                    $currentDate = date('Y-m-d');
                    $sqlRentedDetails = "SELECT c.*, s.username, s.start_date, s.end_date, s.duration, 
                                              DATEDIFF(s.end_date, CURRENT_DATE) as days_remaining
                                       FROM services s 
                                       JOIN cars c ON s.car_id = c.id 
                                       WHERE CURRENT_DATE BETWEEN DATE(s.start_date) AND DATE(s.end_date)
                                       ORDER BY days_remaining ASC";
                    $rentedDetails = $conn->query($sqlRentedDetails);
                    
                    if ($rentedDetails && $rentedDetails->num_rows > 0):
                    ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Car</th>
                                    <th>Rented By</th>
                                    <th>Period</th>
                                    <th>Duration</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($rental = $rentedDetails->fetch_assoc()): 
                                    $startDate = new DateTime($rental['start_date']);
                                    $endDate = new DateTime($rental['end_date']);
                                    $now = new DateTime();
                                    $daysRemaining = $rental['days_remaining'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if(!empty($rental['image']) && file_exists('../' . $rental['image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($rental['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($rental['name']); ?>" 
                                                     style="width: 40px; height: 30px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div style="width: 40px; height: 30px; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                                    <i class="fas fa-car text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="ms-2">
                                                <div class="fw-bold"><?php echo htmlspecialchars($rental['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($rental['brand']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($rental['username']); ?></td>
                                    <td>
                                        <?php echo $startDate->format('M d, Y'); ?><br>
                                        <small class="text-muted">to</small><br>
                                        <?php echo $endDate->format('M d, Y'); ?>
                                    </td>
                                    <td><?php echo $rental['duration']; ?> days</td>
                                    <td>
                                        <?php if ($daysRemaining > 0): ?>
                                            <span class="badge bg-info"><?php echo $daysRemaining; ?> days left</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Due today</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $progress = 100 - (($daysRemaining / $rental['duration']) * 100);
                                        ?>
                                        <div class="progress" style="height: 5px; width: 100px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?php echo $progress; ?>%" 
                                                 aria-valuenow="<?php echo $progress; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-car-alt fa-3x text-muted mb-3"></i>
                        <h5>No Cars Currently Rented</h5>
                        <p class="text-muted">All cars are available for rental.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reserved Cars Modal -->
    <div class="modal fade" id="reservedCarsModal" tabindex="-1" aria-labelledby="reservedCarsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reservedCarsModalLabel">Reserved Cars</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    // Get reserved cars with details
                    $sqlReservedDetails = "SELECT c.*, s.username, s.start_date, s.end_date, s.duration
                                       FROM services s 
                                       JOIN cars c ON s.car_id = c.id 
                                       WHERE DATE(s.start_date) > CURRENT_DATE
                                       ORDER BY s.start_date ASC";
                    $reservedDetails = $conn->query($sqlReservedDetails);
                    
                    if ($reservedDetails && $reservedDetails->num_rows > 0):
                    ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Car</th>
                                    <th>Reserved By</th>
                                    <th>Pickup Date</th>
                                    <th>Return Date</th>
                                    <th>Duration</th>
                                    <th>Days Until Pickup</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($reservation = $reservedDetails->fetch_assoc()): 
                                    $startDate = new DateTime($reservation['start_date']);
                                    $endDate = new DateTime($reservation['end_date']);
                                    $now = new DateTime();
                                    $daysUntilPickup = $now->diff($startDate)->days;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if(!empty($reservation['image']) && file_exists('../' . $reservation['image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($reservation['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($reservation['name']); ?>" 
                                                     style="width: 40px; height: 30px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div style="width: 40px; height: 30px; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                                    <i class="fas fa-car text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="ms-2">
                                                <div class="fw-bold"><?php echo htmlspecialchars($reservation['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($reservation['brand']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                                    <td><?php echo $startDate->format('M d, Y'); ?></td>
                                    <td><?php echo $endDate->format('M d, Y'); ?></td>
                                    <td><?php echo $reservation['duration']; ?> days</td>
                                    <td><span class="badge bg-info"><?php echo $daysUntilPickup; ?> days</span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <h5>No Reserved Cars</h5>
                        <p class="text-muted">There are no future reservations at the moment.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Discount Modal -->
    <?php while($car = $carsResult->fetch_assoc()): ?>
    <div class="modal fade" id="discountModal<?php echo $car['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Discount - <?php echo htmlspecialchars($car['name']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="manage_discount.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Current Price: $<?php echo htmlspecialchars($car['price']); ?></label>
                            <?php if (isset($car['discount_display'])): ?>
                                <div class="text-success">
                                    <i class="fas fa-tag"></i> Current Discount: <?php echo $car['discount_display']; ?> 
                                    (Valid until <?php echo date('M d, Y', strtotime($car['discount_end'])); ?>)
                                    <a href="manage_discount.php?remove=1&car_id=<?php echo $car['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger ms-2"
                                       onclick="return confirm('Are you sure you want to remove this discount?')">
                                        <i class="fas fa-times"></i> Remove
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select" required>
                                <option value="percentage" <?php echo ($car['discount_type'] ?? '') === 'percentage' ? 'selected' : ''; ?>>Percentage Off</option>
                                <option value="fixed" <?php echo ($car['discount_type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>Fixed Amount Off</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" class="form-control" 
                                   min="0" step="0.01" required 
                                   value="<?php echo htmlspecialchars($car['discount_value'] ?? ''); ?>">
                            <div class="form-text">For percentage, enter a number between 0-100. For fixed amount, enter the dollar value.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" required 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo $car['discount_start'] ?? date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" required 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo $car['discount_end'] ?? ''; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Apply Discount</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    
    <!-- Bulk Discount Modal -->
    <div class="modal fade" id="bulkDiscountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Apply Discounts</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Discount Type Tabs -->
                <ul class="nav nav-tabs nav-fill" id="discountTypeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-discount" 
                                type="button" role="tab" aria-controls="bulk-discount" aria-selected="true">
                            <i class="fas fa-layer-group me-2"></i>Bulk Discount
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="selective-tab" data-bs-toggle="tab" data-bs-target="#selective-discount" 
                                type="button" role="tab" aria-controls="selective-discount" aria-selected="false">
                            <i class="fas fa-hand-pointer me-2"></i>Selective Discount
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-4" id="discountTypeContent">
                    <!-- Bulk Discount Tab -->
                    <div class="tab-pane fade show active" id="bulk-discount" role="tabpanel" aria-labelledby="bulk-tab">
                        <form action="manage_discount.php" method="POST">
                            <input type="hidden" name="bulk_discount" value="1">
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Select Target Cars</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Apply Discount To</label>
                                        <select name="discount_scope" class="form-select form-select-lg" required>
                                            <option value="all">All Cars</option>
                                            <option value="available">Available Cars Only</option>
                                            <option value="brand">Specific Brand</option>
                                        </select>
                                    </div>

                                    <div class="mb-3 brand-select" style="display: none;">
                                        <label class="form-label fw-bold">Select Brand</label>
                                        <select name="brand" class="form-select">
                                            <?php foreach($brands as $brand): ?>
                                                <option value="<?php echo htmlspecialchars($brand); ?>"><?php echo htmlspecialchars($brand); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-percentage me-2"></i>Discount Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Discount Type</label>
                                                <select name="discount_type" class="form-select" required>
                                                    <option value="percentage">Percentage Off</option>
                                                    <option value="fixed">Fixed Amount Off</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Discount Value</label>
                                                <div class="input-group">
                                                    <input type="number" name="discount_value" class="form-control" 
                                                        min="0" step="0.01" required>
                                                    <span class="input-group-text discount-symbol">%</span>
                                                </div>
                                                <div class="form-text">For percentage, enter a number between 0-100. For fixed amount, enter the dollar value.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Discount Period</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Start Date</label>
                                                <input type="date" name="start_date" class="form-control" required 
                                                    min="<?php echo date('Y-m-d'); ?>"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">End Date</label>
                                                <input type="date" name="end_date" class="form-control" required 
                                                    min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-lg btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-lg btn-primary ms-2">
                                    <i class="fas fa-check me-2"></i>Apply Bulk Discount
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Selective Discount Tab -->
                    <div class="tab-pane fade" id="selective-discount" role="tabpanel" aria-labelledby="selective-tab">
                        <form action="manage_discount.php" method="POST">
                            <input type="hidden" name="selective_discount" value="1">
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-car me-2"></i>Select Cars</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllCars">
                                        <label class="form-check-label" for="selectAllCars">Select All</label>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="car-selection-list overflow-auto bg-light rounded" style="max-height: 300px;">
                                        <table class="table table-hover mb-0">
                                            <thead class="sticky-top bg-white">
                                                <tr>
                                                    <th width="40"></th>
                                                    <th width="60">Image</th>
                                                    <th>Car Name</th>
                                                    <th>Brand</th>
                                                    <th>Current Price</th>
                                                    <th>Current Discount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Reset the cars result pointer to the beginning
                                                $carsResult->data_seek(0);
                                                while($car = $carsResult->fetch_assoc()): 
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input car-select" type="checkbox" 
                                                                   name="selected_cars[]" value="<?php echo $car['id']; ?>"
                                                                   id="car-<?php echo $car['id']; ?>">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <label for="car-<?php echo $car['id']; ?>">
                                                            <?php if(!empty($car['image']) && file_exists('../' . $car['image'])): ?>
                                                                <img src="../<?php echo htmlspecialchars($car['image']); ?>" 
                                                                    alt="<?php echo htmlspecialchars($car['name']); ?>" 
                                                                    class="img-thumbnail"
                                                                    style="width: 50px; height: 40px; object-fit: cover; cursor: pointer;">
                                                            <?php else: ?>
                                                                <div class="img-thumbnail d-flex align-items-center justify-content-center"
                                                                    style="width: 50px; height: 40px; cursor: pointer;">
                                                                    <i class="fas fa-car text-secondary"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <label for="car-<?php echo $car['id']; ?>" style="cursor: pointer;">
                                                            <?php echo htmlspecialchars($car['name']); ?>
                                                        </label>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($car['brand']); ?></td>
                                                    <td>$<?php echo number_format($car['price'], 2); ?></td>
                                                    <td>
                                                        <?php if (isset($car['discount_display'])): ?>
                                                            <span class="badge bg-success">
                                                                <?php echo $car['discount_display']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">None</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="bg-light p-2 border-top">
                                        <span class="badge bg-primary car-count">0</span> cars selected
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-percentage me-2"></i>Discount Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Discount Type</label>
                                                <select name="discount_type" class="form-select" required>
                                                    <option value="percentage">Percentage Off</option>
                                                    <option value="fixed">Fixed Amount Off</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Discount Value</label>
                                                <div class="input-group">
                                                    <input type="number" name="discount_value" class="form-control" 
                                                        min="0" step="0.01" required>
                                                    <span class="input-group-text selective-discount-symbol">%</span>
                                                </div>
                                                <div class="form-text">For percentage, enter a number between 0-100. For fixed amount, enter the dollar value.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Discount Period</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Start Date</label>
                                                <input type="date" name="start_date" class="form-control" required 
                                                    min="<?php echo date('Y-m-d'); ?>"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">End Date</label>
                                                <input type="date" name="end_date" class="form-control" required 
                                                    min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-lg btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-lg btn-primary ms-2">
                                    <i class="fas fa-check me-2"></i>Apply Selective Discount
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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

        // Show/hide brand select based on discount scope
        document.querySelector('select[name="discount_scope"]').addEventListener('change', function() {
            const brandSelect = document.querySelector('.brand-select');
            brandSelect.style.display = this.value === 'brand' ? 'block' : 'none';
        });
        
        // Selective discount - Handle select all checkbox
        document.getElementById('selectAllCars').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.car-select').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateSelectedCarsCount();
        });
        
        // Update select all state when individual checkboxes are clicked
        document.querySelectorAll('.car-select').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const totalCheckboxes = document.querySelectorAll('.car-select').length;
                const checkedCheckboxes = document.querySelectorAll('.car-select:checked').length;
                
                document.getElementById('selectAllCars').checked = checkedCheckboxes === totalCheckboxes;
                document.getElementById('selectAllCars').indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
                
                updateSelectedCarsCount();
            });
        });
        
        // Update the count of selected cars
        function updateSelectedCarsCount() {
            const checkedCount = document.querySelectorAll('.car-select:checked').length;
            const countBadge = document.querySelector('.car-count');
            if (countBadge) {
                countBadge.textContent = checkedCount;
                
                // Change badge color based on selection
                if (checkedCount === 0) {
                    countBadge.className = 'badge bg-secondary car-count';
                } else {
                    countBadge.className = 'badge bg-primary car-count';
                }
            }
        }
        
        // Update discount symbol (% or $) based on selected discount type
        document.querySelectorAll('select[name="discount_type"]').forEach(select => {
            select.addEventListener('change', function() {
                const isPercentage = this.value === 'percentage';
                
                // Find the closest input-group-text that represents the discount symbol
                let symbolElement;
                if (this.closest('#bulk-discount')) {
                    symbolElement = document.querySelector('.discount-symbol');
                } else if (this.closest('#selective-discount')) {
                    symbolElement = document.querySelector('.selective-discount-symbol');
                } else {
                    // For individual car discount modals
                    symbolElement = this.closest('.modal-content').querySelector('.input-group-text');
                }
                
                if (symbolElement) {
                    symbolElement.textContent = isPercentage ? '%' : '$';
                    
                    // Add a highlight animation to notify the user of the change
                    symbolElement.classList.add('text-primary', 'fw-bold');
                    setTimeout(() => {
                        symbolElement.classList.remove('text-primary', 'fw-bold');
                    }, 500);
                }
            });
        });
        
        // Run this on page load to ensure the discount count is initialized
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedCarsCount();
            
            // Add animation to modal when it appears
            document.querySelector('#bulkDiscountModal').addEventListener('shown.bs.modal', function() {
                const cards = this.querySelectorAll('.card');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                        card.style.transform = 'translateY(0)';
                        card.style.opacity = '1';
                    }, index * 100);
                });
            });
            
            // Reset animation when modal is hidden
            document.querySelector('#bulkDiscountModal').addEventListener('hide.bs.modal', function() {
                const cards = this.querySelectorAll('.card');
                cards.forEach(card => {
                    card.style.transform = 'translateY(20px)';
                    card.style.opacity = '0';
                });
            });
        });
        
        // Make the tag buttons work properly in the table
        document.querySelectorAll('.admin-btn-info').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // The button is already configured to open the modal via data-bs-toggle and data-bs-target
                // This just ensures it works properly
                e.preventDefault();
                const targetModalId = this.getAttribute('data-bs-target');
                if (targetModalId) {
                    const modal = new bootstrap.Modal(document.querySelector(targetModalId));
                    modal.show();
                }
            });
        });
        
        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Form validation for the discount forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (form.querySelector('input[name="selective_discount"]')) {
                    const selectedCars = form.querySelectorAll('input[name="selected_cars[]"]:checked');
                    if (selectedCars.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one car to apply the discount.');
                    }
                }
            });
        });
    </script>
    
    <style>
        /* Additional styles for modal animations */
        #bulkDiscountModal .card {
            transform: translateY(20px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        .nav-tabs .nav-link {
            position: relative;
            transition: all 0.3s ease;
            border-bottom: none;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 16px;
        }
        
        .nav-tabs .nav-link.active {
            color: #4299e1;
            font-weight: 600;
        }
        
        .nav-tabs .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: #4299e1;
            transform: scaleX(1);
        }
        
        .nav-tabs .nav-link:hover {
            color: #4299e1;
        }
        
        .table-hover tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(66, 153, 225, 0.08) !important;
        }
        
        .form-check-input:checked {
            background-color: #4299e1;
            border-color: #4299e1;
        }
        
        .input-group-text.discount-symbol,
        .input-group-text.selective-discount-symbol {
            transition: all 0.3s ease;
        }
        
        .car-count {
            display: inline-flex;
            min-width: 24px;
            height: 24px;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</body>
</html>