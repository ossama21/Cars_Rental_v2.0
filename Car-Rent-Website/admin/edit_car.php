<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

checkAdminAccess();

// Create tables if they don't exist
$sqlFile = file_get_contents('car_images.sql');
$conn->multi_query($sqlFile);
while ($conn->more_results()) {
    $conn->next_result();
}

// Add quantity to database if it doesn't exist
$conn->query("ALTER TABLE cars ADD COLUMN IF NOT EXISTS quantity INT NOT NULL DEFAULT 1");

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

// Initialize variables
$car = [];
$errors = [];

// Get car details and images
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $carId = $_GET['id'];
    
    // Get car details
    $sqlCar = "SELECT * FROM cars WHERE id = ?";
    $stmtCar = $conn->prepare($sqlCar);
    $stmtCar->bind_param("i", $carId);
    $stmtCar->execute();
    $resultCar = $stmtCar->get_result();
    
    if ($resultCar->num_rows > 0) {
        $car = $resultCar->fetch_assoc();
        
        // Get car images
        $sqlImages = "SELECT * FROM car_images WHERE car_id = ? ORDER BY is_primary DESC";
        $stmtImages = $conn->prepare($sqlImages);
        $stmtImages->bind_param("i", $carId);
        $stmtImages->execute();
        $car['images'] = $stmtImages->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        header("Location: manage_cars.php?error=Car not found");
        exit;
    }
} else {
    header("Location: manage_cars.php?error=No car specified");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data with error checking
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $model = isset($_POST['model']) ? trim($_POST['model']) : '';
    $transmission = isset($_POST['transmission']) ? trim($_POST['transmission']) : '';
    $interior = isset($_POST['interior']) ? trim($_POST['interior']) : '';
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $engine_type = isset($_POST['engine_type']) ? trim($_POST['engine_type']) : '';
    $fuel_type = isset($_POST['fuel_type']) ? trim($_POST['fuel_type']) : '';
    $seating_capacity = isset($_POST['seating_capacity']) ? trim($_POST['seating_capacity']) : '';
    $mileage = isset($_POST['mileage']) ? trim($_POST['mileage']) : '';
    $features = isset($_POST['features']) ? trim($_POST['features']) : '';
    $year = isset($_POST['year']) ? trim($_POST['year']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $registration_number = isset($_POST['registration_number']) ? trim($_POST['registration_number']) : '';
    $vin = isset($_POST['vin']) ? trim($_POST['vin']) : '';

    // Debug information
    error_log("Debug - Parameters for car update:");
    error_log("Name: " . $name);
    error_log("Price: " . $price);
    error_log("Description: " . $description);
    error_log("Model: " . $model);
    error_log("Car ID: " . $carId);
    
    // Validate required fields with detailed error messages
    if (empty($name)) $errors[] = "Car name is required";
    if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required";
    if (empty($year)) $errors[] = "Model year is required";
    if (empty($brand)) $errors[] = "Brand is required";
    if ($quantity < 1) $errors[] = "Quantity must be at least 1";

    // Prepare features for database - ensure proper formatting
    if (!empty($features)) {
        // Split features by comma and clean them
        $featureArray = array_map('trim', explode(',', $features));
        // Ensure each feature has brackets
        $featureArray = array_map(function($feature) {
            $feature = trim($feature, '[]');
            return '[' . $feature . ']';
        }, $featureArray);
        // Join back for database
        $features = implode(', ', $featureArray);
    }
    
    if (empty($errors)) {
        try {
            $conn->begin_transaction();
            
            // Update car details with error handling
            $sqlUpdate = "UPDATE cars SET 
                name=?, 
                price=?, 
                description=?, 
                year=?,
                transmission=?, 
                interior=?, 
                brand=?,
                quantity=?,
                features=?,
                engine_type=?,
                fuel_type=?,
                seating_capacity=?,
                mileage=?,
                color=?,
                registration_number=?,
                vin=?
                WHERE id=?";
                
            error_log("SQL Query: " . $sqlUpdate);
            
            if (!($updateStmt = $conn->prepare($sqlUpdate))) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            // Define parameter types and create parameter array - match exact database schema
            $params = array(
                $name,              // string (name)
                $price,            // decimal (price)
                $description,      // string (description)
                $year,             // string (year)
                $transmission,     // string (transmission)
                $interior,         // string (interior)
                $brand,           // string (brand)
                $quantity,        // integer (quantity)
                $features,        // string (features)
                $engine_type,     // string (engine_type)
                $fuel_type,       // string (fuel_type)
                $seating_capacity, // string (seating_capacity)
                $mileage,         // string (mileage)
                $color,           // string (color)
                $registration_number, // string (registration_number)
                $vin,             // string (vin)
                $carId            // integer (WHERE id)
            );
            
            // Match types with database columns
            $types = "sdsssssissssssssi";
            
            error_log("Number of parameters: " . count($params));
            error_log("Length of types string: " . strlen($types));
            
            if (!$updateStmt->bind_param($types, ...$params)) {
                throw new Exception("Parameter binding failed: " . $updateStmt->error);
            }
            
            if (!$updateStmt->execute()) {
                throw new Exception("Execute failed: " . $updateStmt->error);
            }

            // Handle image uploads
            if (!empty($_FILES['car_images']['name'][0])) {
                // Create directory for car images if it doesn't exist
                $carImageDir = "../images/cars/" . $carId;
                if (!file_exists($carImageDir)) {
                    mkdir($carImageDir, 0777, true);
                }
                
                // Handle multiple image uploads
                $uploadedImages = 0;
                $maxImages = 4; // Maximum number of images per car
                $currentImages = count($car['images']);
                
                foreach ($_FILES['car_images']['tmp_name'] as $key => $tmp_name) {
                    if ($currentImages + $uploadedImages >= $maxImages) break;
                    
                    if (!empty($tmp_name) && is_uploaded_file($tmp_name)) {
                        $fileName = $_FILES['car_images']['name'][$key];
                        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        
                        // Allow certain file formats
                        if ($fileType == "jpg" || $fileType == "jpeg" || $fileType == "png") {
                            // Generate unique filename
                            $newFileName = uniqid() . '.' . $fileType;
                            $targetPath = $carImageDir . '/' . $newFileName;
                            
                            if (move_uploaded_file($tmp_name, $targetPath)) {
                                // Insert image record
                                $relativePath = "images/cars/" . $carId . '/' . $newFileName;
                                $isPrimary = ($currentImages + $uploadedImages == 0) ? 1 : 0; // First image is primary if no other images exist
                                
                                $sqlImage = "INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, ?)";
                                $stmtImage = $conn->prepare($sqlImage);
                                $stmtImage->bind_param("isi", $carId, $relativePath, $isPrimary);
                                $stmtImage->execute();
                                
                                $uploadedImages++;
                            }
                        }
                    }
                }
            }
            
            $conn->commit();
            $_SESSION['success'] = "Car updated successfully" . ($uploadedImages > 0 ? " with $uploadedImages new images." : ".");
            header("Location: manage_cars.php");
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error in car update: " . $e->getMessage());
            $errors[] = "Error updating car: " . $e->getMessage();
        }
    }
}

// Get brands for dropdown (for autocomplete suggestions)
$sqlBrands = "SELECT DISTINCT brand FROM cars ORDER BY brand";
$brandsResult = $conn->query($sqlBrands);
$brands = [];
while ($brandRow = $brandsResult->fetch_assoc()) {
    $brands[] = $brandRow['brand'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car - Admin Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/modern.css">
    
    <style>
        .edit-car-section {
            padding: 2rem;
            background-color: var(--light-bg);
            min-height: calc(100vh - 60px);
        }
        
        .edit-car-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .form-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .image-upload-zone {
            border: 2px dashed var(--gray-300);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: var(--gray-100);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .image-upload-zone:hover {
            border-color: var(--primary-color);
            background: var(--gray-100);
        }
        
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .image-preview-item {
            position: relative;
            aspect-ratio: 3/2;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .image-preview-item:hover .remove-image {
            opacity: 1;
            transform: translateY(0);
        }
        
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .remove-image {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 59, 48, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
            z-index: 2;
        }
        
        .remove-image:hover {
            background: rgb(255, 59, 48);
            transform: scale(1.1);
        }

        .image-preview-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.2);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .image-preview-item:hover::before {
            opacity: 1;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid var(--gray-300);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(49,130,206,0.1);
        }
        
        .required-asterisk {
            color: var(--error-color);
            margin-left: 4px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(49,130,206,0.15);
        }
        
        .form-hint {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }
        
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .dragover {
            border-color: var(--primary-color);
            background: rgba(49,130,206,0.05);
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

        .sidebar-menu-link {
            padding: 10px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .sidebar-menu-link:hover, 
        .sidebar-menu-link.active {
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

        .submenu-item a {
            padding: 0.5rem 1.5rem 0.5rem 3.5rem;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
        }

        .submenu-item a:hover,
        .submenu-item a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .image-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 8px;
            display: flex;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .image-preview-item:hover .image-actions {
            opacity: 1;
        }

        .form-check-label {
            color: white;
            font-size: 0.9rem;
            margin-left: 4px;
        }

        .primary-image-selector {
            cursor: pointer;
        }

        .features-input-container {
            position: relative;
        }

        .feature-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .feature-tag {
            display: inline-flex;
            align-items: center;
            background-color: #e2f0fd;
            color: #0c63e4;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
        }

        .remove-feature {
            cursor: pointer;
            margin-left: 6px;
            color: #0c63e4;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .remove-feature:hover {
            opacity: 1;
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
                
                <div class="sidebar-menu-item mt-2">
                    <a href="../index.php" class="sidebar-menu-link">
                        <i class="fas fa-home"></i>
                        <span>Back to Home</span>
                    </a>
                </div>
            </nav>
        </aside>

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
            
            <div class="edit-car-section">
                <div class="edit-car-container">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-0">Edit Car</h1>
                            <p class="text-muted">Update vehicle information and images</p>
                        </div>
                        <a href="manage_cars.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Cars
                        </a>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $carId); ?>" method="post" enctype="multipart/form-data">
                        <!-- Images Section -->
                        <div class="form-card">
                            <div class="form-section-title">
                                <i class="fas fa-images"></i>
                                Car Images
                            </div>
                            <div class="image-upload-zone" id="imageUploadZone">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                <h5 class="mb-2">Drag and drop images here or click to browse</h5>
                                <p class="text-muted mb-0">Supports: JPG, JPEG, PNG (Max 5MB each)</p>
                                <input type="file" id="car_images" name="car_images[]" multiple accept="image/*" class="d-none">
                            </div>
                            
                            <div class="image-preview-grid" id="imagePreviewGrid">
                                <?php foreach ($car['images'] as $image): ?>
                                <div class="image-preview-item" data-image-id="<?php echo $image['id']; ?>">
                                    <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Car Image">
                                    <button type="button" class="remove-image" onclick="removeExistingImage(<?php echo $image['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div class="image-actions">
                                        <div class="form-check">
                                            <input class="form-check-input primary-image-selector" type="radio" 
                                                   name="primary_image" value="<?php echo $image['id']; ?>"
                                                   <?php echo $image['is_primary'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Primary</label>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="form-card">
                            <div class="form-section-title">
                                <i class="fas fa-info-circle"></i>
                                Basic Information
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Car Name<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($car['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Brand<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-control" name="brand" list="brandList" 
                                               value="<?php echo htmlspecialchars($car['brand']); ?>" required>
                                        <datalist id="brandList">
                                            <?php foreach ($brands as $brand): ?>
                                                <option value="<?php echo htmlspecialchars($brand); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Daily Rate<span class="required-asterisk">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" name="price" min="0" step="0.01" 
                                                   value="<?php echo htmlspecialchars($car['price']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Model Year<span class="required-asterisk">*</span></label>
                                        <input type="number" class="form-control" name="year" 
                                               value="<?php echo htmlspecialchars($car['year']); ?>"
                                               min="1900" max="<?php echo date('Y') + 1; ?>" required>
                                        <input type="hidden" name="model" value="<?php echo htmlspecialchars($car['year']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Quantity Available<span class="required-asterisk">*</span></label>
                                        <input type="number" class="form-control" name="quantity" min="1" 
                                               value="<?php echo htmlspecialchars($car['quantity']); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicle Specifications -->
                        <div class="form-card">
                            <div class="form-section-title">
                                <i class="fas fa-cogs"></i>
                                Vehicle Specifications
                            </div>
                            <div class="specs-grid">
                                <div class="form-group">
                                    <label class="form-label">Transmission</label>
                                    <select class="form-control" name="transmission">
                                        <option value="Automatic" <?php echo $car['transmission'] === 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                                        <option value="Manual" <?php echo $car['transmission'] === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                                        <option value="Semi-Automatic" <?php echo $car['transmission'] === 'Semi-Automatic' ? 'selected' : ''; ?>>Semi-Automatic</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Engine Type</label>
                                    <input type="text" class="form-control" name="engine_type" 
                                           value="<?php echo htmlspecialchars($car['engine_type']); ?>" 
                                           placeholder="e.g. 2.0L Turbo">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Fuel Type</label>
                                    <select class="form-control" name="fuel_type">
                                        <option value="Petrol" <?php echo $car['fuel_type'] === 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                                        <option value="Diesel" <?php echo $car['fuel_type'] === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                                        <option value="Electric" <?php echo $car['fuel_type'] === 'Electric' ? 'selected' : ''; ?>>Electric</option>
                                        <option value="Hybrid" <?php echo $car['fuel_type'] === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Interior Type</label>
                                    <select class="form-control" name="interior">
                                        <option value="Fabric" <?php echo $car['interior'] === 'Fabric' ? 'selected' : ''; ?>>Fabric</option>
                                        <option value="Leather" <?php echo $car['interior'] === 'Leather' ? 'selected' : ''; ?>>Leather</option>
                                        <option value="Sport" <?php echo $car['interior'] === 'Sport' ? 'selected' : ''; ?>>Sport</option>
                                        <option value="Premium" <?php echo $car['interior'] === 'Premium' ? 'selected' : ''; ?>>Premium</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Seating Capacity</label>
                                    <input type="number" class="form-control" name="seating_capacity" 
                                           value="<?php echo htmlspecialchars($car['seating_capacity']); ?>" 
                                           min="1" max="50">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Mileage</label>
                                    <input type="text" class="form-control" name="mileage" 
                                           value="<?php echo htmlspecialchars($car['mileage']); ?>" 
                                           placeholder="e.g. 15 km/l">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Details -->
                        <div class="form-card">
                            <div class="form-section-title">
                                <i class="fas fa-clipboard-list"></i>
                                Additional Details
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Color</label>
                                        <input type="text" class="form-control" name="color" 
                                               value="<?php echo htmlspecialchars($car['color']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Registration Number</label>
                                        <input type="text" class="form-control" name="registration_number" 
                                               value="<?php echo htmlspecialchars($car['registration_number']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">VIN (Vehicle Identification Number)</label>
                                <input type="text" class="form-control" name="vin" 
                                       value="<?php echo htmlspecialchars($car['vin']); ?>" 
                                       placeholder="Vehicle Identification Number">
                            </div>
                            
                            <div class="form-group">
                                <label for="features">Features</label>
                                <div class="features-input-container">
                                    <input type="text" id="feature-input" class="form-control" placeholder="Type a feature and press Enter">
                                    <input type="hidden" id="features" name="features" value="<?php echo htmlspecialchars($car['features']); ?>">
                                    <small class="form-text text-muted">Press Enter after typing each feature. Features will be automatically formatted with brackets.</small>
                                </div>
                                <div class="feature-tags" id="feature-tags-container">
                                    <?php 
                                    if (!empty($car['features'])) {
                                        $features = array_map('trim', explode(',', $car['features']));
                                        foreach ($features as $feature) {
                                            if (!empty($feature)) {
                                                echo '<span class="feature-tag">' . htmlspecialchars($feature) . ' <i class="fas fa-times remove-feature"></i></span>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="form-group mb-0">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4" 
                                          placeholder="Enter detailed description of the vehicle"><?php echo htmlspecialchars($car['description']); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $carId; ?>)">
                                    <i class="fas fa-trash me-2"></i>Delete Car
                                </button>
                                
                                <div class="d-flex gap-3">
                                    <a href="manage_cars.php" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Image upload handling
        const imageUploadZone = document.getElementById('imageUploadZone');
        const fileInput = document.getElementById('car_images');
        const imagePreviewGrid = document.getElementById('imagePreviewGrid');
        let selectedFiles = [];

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            imageUploadZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            imageUploadZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            imageUploadZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            imageUploadZone.classList.add('dragover');
        }

        function unhighlight() {
            imageUploadZone.classList.remove('dragover');
        }

        imageUploadZone.addEventListener('click', () => fileInput.click());
        imageUploadZone.addEventListener('drop', handleDrop);
        fileInput.addEventListener('change', handleFiles);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles({ target: { files } });
        }

        function handleFiles(e) {
            const files = e.target.files;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            const maxFiles = 4;
            const maxSize = 5 * 1024 * 1024; // 5MB

            // Filter and validate files
            const validFiles = Array.from(files).filter(file => {
                if (!allowedTypes.includes(file.type)) {
                    alert(`File "${file.name}" is not a supported image format`);
                    return false;
                }
                if (file.size > maxSize) {
                    alert(`File "${file.name}" is too large. Maximum size is 5MB`);
                    return false;
                }
                return true;
            });

            // Check total number of files
            const currentCount = document.querySelectorAll('.image-preview-item').length;
            if (currentCount + validFiles.length > maxFiles) {
                alert(`You can only upload up to ${maxFiles} images`);
                return;
            }

            // Add previews
            validFiles.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'image-preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-image">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    previewItem.querySelector('.remove-image').addEventListener('click', function() {
                        previewItem.remove();
                    });
                    imagePreviewGrid.appendChild(previewItem);
                }
                reader.readAsDataURL(file);
            });
        }

        // Remove existing image
        function removeExistingImage(imageId) {
            if (confirm('Are you sure you want to remove this image?')) {
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.style.opacity = '0.5'; // Visual feedback that deletion is in progress
                }
                
                fetch(`remove_car_image.php?id=${imageId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (imageElement) {
                            imageElement.remove();
                        }
                        // Check if we need to update the image upload zone status
                        const remainingImages = document.querySelectorAll('.image-preview-item').length;
                        if (remainingImages < 4) {
                            imageUploadZone.style.display = 'block';
                        }
                    } else {
                        if (imageElement) {
                            imageElement.style.opacity = '1'; // Restore opacity if failed
                        }
                        alert('Failed to remove image: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (imageElement) {
                        imageElement.style.opacity = '1'; // Restore opacity if failed
                    }
                    alert('Failed to remove image. Please try again.');
                });
            }
        }

        // Confirm delete
        function confirmDelete(carId) {
            if (confirm('Are you sure you want to delete this car? This action cannot be undone.')) {
                window.location.href = `delete_car.php?id=${carId}`;
            }
        }

        // Sidebar toggle
        const toggleSidebar = document.getElementById('toggleSidebar');
        const adminWrapper = document.querySelector('.admin-wrapper');
        
        toggleSidebar.addEventListener('click', function() {
            adminWrapper.classList.toggle('sidebar-collapsed');
        });
        
        // Responsive sidebar
        if (window.innerWidth < 992) {
            adminWrapper.classList.add('sidebar-collapsed');
        }
        
        window.addEventListener('resize', function() {
            if (window.innerWidth < 992) {
                adminWrapper.classList.add('sidebar-collapsed');
            }
        });

        // Sync model with year
        const yearInput = document.querySelector('input[name="year"]');
        const modelInput = document.querySelector('input[name="model"]');
        yearInput.addEventListener('change', function() {
            modelInput.value = this.value;
        });

        // Add event listener for primary image selection
        document.querySelectorAll('.primary-image-selector').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    updatePrimaryImage(this.value);
                }
            });
        });

        function updatePrimaryImage(imageId) {
            fetch('update_primary_image.php?id=' + imageId, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to set primary image: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to set primary image. Please try again.');
            });
        }

        // Feature tags handling
        document.addEventListener('DOMContentLoaded', function() {
            const featureInput = document.getElementById('feature-input');
            const featuresHidden = document.getElementById('features');
            const featureTagsContainer = document.getElementById('feature-tags-container');
            
            function addFeature(featureText) {
                if (featureText && featureText.trim() !== '') {
                    featureText = featureText.trim();
                    // Add brackets if not present
                    if (!featureText.startsWith('[')) featureText = '[' + featureText;
                    if (!featureText.endsWith(']')) featureText = featureText + ']';
                    
                    const featureTag = document.createElement('span');
                    featureTag.className = 'feature-tag';
                    featureTag.innerHTML = featureText + ' <i class="fas fa-times remove-feature"></i>';
                    featureTagsContainer.appendChild(featureTag);
                    
                    featureInput.value = '';
                    updateFeaturesInput();
                }
            }
            
            function updateFeaturesInput() {
                const featureTags = document.querySelectorAll('.feature-tag');
                const featuresArray = Array.from(featureTags).map(tag => 
                    tag.textContent.trim().replace(/×$/, '').trim()
                );
                featuresHidden.value = featuresArray.join(', ');
            }
            
            // Add feature when pressing Enter
            featureInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addFeature(this.value);
                }
            });
            
            // Initialize existing features
            if (featuresHidden.value) {
                const existingFeatures = featuresHidden.value.split(',').map(f => f.trim()).filter(f => f);
                existingFeatures.forEach(feature => addFeature(feature));
            }
            
            // Remove feature when clicking the x button
            featureTagsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-feature')) {
                    e.target.parentElement.remove();
                    updateFeaturesInput();
                }
            });
        });
    </script>
</body>
</html>