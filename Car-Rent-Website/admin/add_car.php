<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Use the auth function to check admin access
checkAdminAccess();

// Create tables if they don't exist
$sqlFile = file_get_contents('car_images.sql');
$conn->multi_query($sqlFile);
while ($conn->more_results()) {
    $conn->next_result();
}

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $model = $_POST['model'];
    $transmission = $_POST['transmission'];
    $interior = $_POST['interior'];
    $brand = $_POST['brand'];
    $engine_type = $_POST['engine_type'];
    $fuel_type = $_POST['fuel_type'];
    $seating_capacity = $_POST['seating_capacity'];
    $mileage = $_POST['mileage'];
    $features = $_POST['features'];
    $year = $_POST['year'];
    $color = $_POST['color'];
    $registration_number = $_POST['registration_number'];
    $vin = $_POST['vin'];
    $quantity = $_POST['quantity'];
    
    // Initialize error message array
    $errors = [];
    
    // Validate required fields
    if (empty($name)) $errors[] = "Car name is required";
    if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required";
    if (empty($year)) $errors[] = "Year is required";
    if (empty($brand)) $errors[] = "Brand is required";
    if ($quantity < 1) $errors[] = "Quantity must be at least 1";
    
    // If no errors, proceed with database insertion and image uploads
    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            // Insert car details first
            $sqlInsert = "INSERT INTO cars (name, price, description, model, transmission, interior, brand, engine_type, 
                         fuel_type, seating_capacity, mileage, features, year, color, registration_number, vin, quantity) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($sqlInsert);
            $insertStmt->bind_param("sdsssssssississsi", 
                $name, $price, $description, $model, $transmission, $interior, $brand, $engine_type,
                $fuel_type, $seating_capacity, $mileage, $features, $year, $color, $registration_number, $vin, $quantity
            );
            
            if ($insertStmt->execute()) {
                $car_id = $conn->insert_id;
                
                // Create directory for car images if it doesn't exist
                $carImageDir = "../images/cars/" . $car_id;
                if (!file_exists($carImageDir)) {
                    mkdir($carImageDir, 0777, true);
                }
                
                // Handle multiple image uploads
                $uploadedImages = 0;
                $maxImages = 4; // Maximum number of images per car
                
                foreach ($_FILES['car_images']['tmp_name'] as $key => $tmp_name) {
                    if ($uploadedImages >= $maxImages) break;
                    
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
                                $relativePath = "images/cars/" . $car_id . '/' . $newFileName;
                                $isPrimary = ($uploadedImages == 0) ? 1 : 0; // First image is primary
                                
                                $sqlImage = "INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, ?)";
                                $stmtImage = $conn->prepare($sqlImage);
                                $stmtImage->bind_param("isi", $car_id, $relativePath, $isPrimary);
                                $stmtImage->execute();
                                
                                $uploadedImages++;
                            }
                        }
                    }
                }
                
                $conn->commit();
                $_SESSION['success'] = "Car added successfully with " . $uploadedImages . " images.";
                header("Location: manage_cars.php");
                exit;
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

// Get brands for dropdown
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
    <title>Add New Car - Admin Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/modern.css">
    
    <!-- Add inline CSS for this specific page -->
    <style>
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
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
        
        .admin-main {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .form-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .form-body {
            padding: 30px;
        }
        
        .image-preview-container {
            width: 100%;
            height: 200px;
            border: 2px dashed #cbd5e0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8fafc;
            overflow: hidden;
            position: relative;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 100%;
        }
        
        .upload-placeholder {
            text-align: center;
        }
        
        .upload-placeholder i {
            font-size: 2.5rem;
            color: #cbd5e0;
            margin-bottom: 10px;
        }
        
        .field-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .required-asterisk {
            color: #e53e3e;
            margin-left: 3px;
        }
        
        .form-hint {
            font-size: 0.8rem;
            color: #718096;
            margin-top: 5px;
        }
        
        .input-group-text {
            background-color: #f8fafc;
        }
        
        .admin-btn-lg {
            padding: 12px 24px;
            font-size: 1rem;
        }
        
        .admin-btn-primary {
            background-color: #3182ce;
            color: white;
            border: none;
            border-radius: 0.375rem;
            transition: all 0.3s;
        }
        
        .admin-btn-primary:hover {
            background-color: #2c5282;
            color: white;
        }
        
        .admin-btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .admin-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #fff;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-user-info {
            text-align: right;
        }
        
        .admin-user-name {
            font-weight: 600;
        }
        
        .admin-user-role {
            font-size: 0.8rem;
            color: #718096;
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
        }
        
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .image-preview-item {
            position: relative;
            aspect-ratio: 4/3;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-preview-item .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #e53e3e;
        }
        
        .drag-drop-zone {
            border: 2px dashed #cbd5e0;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            background-color: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .drag-drop-zone:hover {
            border-color: #3182ce;
            background-color: #ebf8ff;
        }
        
        .drag-drop-zone.dragover {
            border-color: #3182ce;
            background-color: #ebf8ff;
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
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link has-submenu" id="reportsMenu">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
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
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Car</h1>
                        <p class="text-muted">Add a new vehicle to your rental fleet</p>
                    </div>
                    <div>
                        <a href="manage_cars.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Cars
                        </a>
                    </div>
                </div>
                
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Add Car Form -->
                <div class="form-card">
                    <div class="form-header">
                        <h5 class="mb-0">Add New Car</h5>
                    </div>
                    <div class="form-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                            <!-- Images Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label">Car Images (Up to 4 images)</label>
                                    <div class="drag-drop-zone" id="dragDropZone">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                        <p class="mb-0">Drag and drop images here or click to select files</p>
                                        <small class="text-muted">Supports: JPG, JPEG, PNG (Max 5MB each)</small>
                                    </div>
                                    <input type="file" id="car_images" name="car_images[]" multiple accept="image/*" class="d-none">
                                    <div class="image-preview-grid" id="imagePreviewGrid"></div>
                                </div>
                            </div>

                            <!-- Basic Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Car Name *</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Brand *</label>
                                        <input type="text" class="form-control" name="brand" list="brandList" required>
                                        <datalist id="brandList">
                                            <?php foreach ($brands as $brand): ?>
                                                <option value="<?php echo htmlspecialchars($brand); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Details -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Year *</label>
                                        <input type="number" class="form-control" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Price per Day *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity Available *</label>
                                        <input type="number" class="form-control" name="quantity" min="1" value="1" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Technical Specifications -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Engine Type</label>
                                        <input type="text" class="form-control" name="engine_type" placeholder="e.g. 2.0L Turbo">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Transmission</label>
                                        <select class="form-select" name="transmission">
                                            <option value="Automatic">Automatic</option>
                                            <option value="Manual">Manual</option>
                                            <option value="Semi-Automatic">Semi-Automatic</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Fuel Type</label>
                                        <select class="form-select" name="fuel_type">
                                            <option value="Petrol">Petrol</option>
                                            <option value="Diesel">Diesel</option>
                                            <option value="Electric">Electric</option>
                                            <option value="Hybrid">Hybrid</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Details -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Seating Capacity</label>
                                        <input type="number" class="form-control" name="seating_capacity" min="1" max="50">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Mileage</label>
                                        <input type="text" class="form-control" name="mileage" placeholder="e.g. 15 km/l">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Color</label>
                                        <input type="text" class="form-control" name="color">
                                    </div>
                                </div>
                            </div>

                            <!-- Registration Details -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Registration Number</label>
                                        <input type="text" class="form-control" name="registration_number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">VIN</label>
                                        <input type="text" class="form-control" name="vin" placeholder="Vehicle Identification Number">
                                    </div>
                                </div>
                            </div>

                            <!-- Features and Description -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Features</label>
                                        <div class="features-input-container">
                                            <input type="text" id="features" name="features" class="form-control" placeholder="Add features and press Enter">
                                            <small class="form-text text-muted">Type a feature and press Enter to add it. Each feature will be automatically formatted with brackets.</small>
                                        </div>
                                        <div class="feature-tags" id="feature-tags-container"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Car
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle sidebar functionality
        const toggleSidebar = document.getElementById('toggleSidebar');
        const adminWrapper = document.getElementById('adminWrapper');
        const adminSidebar = document.getElementById('adminSidebar');
        
        toggleSidebar.addEventListener('click', function() {
            adminWrapper.classList.toggle('sidebar-collapsed');
            if (window.innerWidth < 992) {
                adminSidebar.classList.toggle('show');
            }
        });
        
        // Drag and drop functionality for multiple images
        const dragDropZone = document.getElementById('dragDropZone');
        const fileInput = document.getElementById('car_images');
        const imagePreviewGrid = document.getElementById('imagePreviewGrid');
        let selectedFiles = [];

        dragDropZone.addEventListener('click', () => fileInput.click());

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dragDropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dragDropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dragDropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dragDropZone.classList.add('dragover');
        }

        function unhighlight(e) {
            dragDropZone.classList.remove('dragover');
        }

        dragDropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            const maxFiles = 4;
            const maxSize = 5 * 1024 * 1024; // 5MB

            // Convert FileList to Array and filter
            const newFiles = Array.from(files).filter(file => {
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
            if (selectedFiles.length + newFiles.length > maxFiles) {
                alert(`You can only upload up to ${maxFiles} images`);
                return;
            }

            // Add new files to selected files array
            selectedFiles = [...selectedFiles, ...newFiles];
            updateImagePreviews();
        }

        function updateImagePreviews() {
            imagePreviewGrid.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'image-preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-image" onclick="removeImage(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    imagePreviewGrid.appendChild(previewItem);
                }
                reader.readAsDataURL(file);
            });

            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            fileInput.files = dataTransfer.files;
        }

        function removeImage(index) {
            selectedFiles.splice(index, 1);
            updateImagePreviews();
        }

        // Feature tags handling
        document.addEventListener('DOMContentLoaded', function() {
            const featuresInput = document.getElementById('features');
            const featureTagsContainer = document.getElementById('feature-tags-container');
            
            // Function to update the hidden input with all feature tags
            function updateFeaturesInput() {
                const featureTags = document.querySelectorAll('.feature-tag');
                const featuresArray = Array.from(featureTags).map(tag => {
                    return tag.textContent.trim().replace('×', '').trim();
                });
                featuresInput.value = featuresArray.join(', ');
            }
            
            // Handle Enter key press
            featuresInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const featureInput = this.value.trim();
                    if (featureInput) {
                        addFeatureTag(featureInput);
                        this.value = ''; // Clear input after adding
                    }
                }
            });
            
            function addFeatureTag(featureText) {
                // Format with brackets if not already present
                if (!featureText.startsWith('[')) {
                    featureText = '[' + featureText;
                }
                if (!featureText.endsWith(']')) {
                    featureText = featureText + ']';
                }
                
                const featureTag = document.createElement('span');
                featureTag.className = 'feature-tag';
                featureTag.innerHTML = featureText + ' <i class="fas fa-times remove-feature"></i>';
                featureTagsContainer.appendChild(featureTag);
                
                // Add event listener to the remove button
                const removeBtn = featureTag.querySelector('.remove-feature');
                removeBtn.addEventListener('click', function() {
                    featureTag.remove();
                    updateFeaturesInput();
                });
                
                updateFeaturesInput();
            }
            
            // Remove feature tags when clicking the "x" button
            document.querySelectorAll('.remove-feature').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.parentElement.remove();
                    updateFeaturesInput();
                });
            });
        });
    </script>
</body>
</html>
