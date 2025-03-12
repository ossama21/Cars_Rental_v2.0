<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Use the auth function to check admin access
checkAdminAccess();

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

// Check if car ID is provided
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
    } else {
        // Redirect to manage cars if car not found
        header("Location: manage_cars.php?error=Car not found");
        exit;
    }
} else {
    // Redirect to manage cars if no ID provided
    header("Location: manage_cars.php?error=No car specified");
    exit;
}

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
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Validate required fields
    if (empty($name)) $errors[] = "Car name is required";
    if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required";
    if (empty($model)) $errors[] = "Model year is required";
    if (empty($brand)) $errors[] = "Brand is required";
    if ($quantity < 1) $errors[] = "Quantity must be at least 1";
    
    // If no errors, proceed with image upload (if provided) and database update
    if (empty($errors)) {
        $uploadOk = 1;
        $relativePath = $car['image']; // Default to current image
        
        // Check if a new image was uploaded
        if(isset($_FILES["carImage"]) && $_FILES["carImage"]["tmp_name"] != "") {
            $targetDir = "../images/";
            $fileName = basename($_FILES["carImage"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            
            $check = getimagesize($_FILES["carImage"]["tmp_name"]);
            if($check !== false) {
                // Allow certain file formats
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                    $errors[] = "Sorry, only JPG, JPEG, PNG files are allowed.";
                    $uploadOk = 0;
                }
                
                // Check file size (limit to 5MB)
                if ($_FILES["carImage"]["size"] > 5000000) {
                    $errors[] = "Sorry, your file is too large. Max 5MB allowed.";
                    $uploadOk = 0;
                }
                
                // If everything is ok, try to upload file
                if ($uploadOk) {
                    if (move_uploaded_file($_FILES["carImage"]["tmp_name"], $targetFilePath)) {
                        $relativePath = "images/" . $fileName;
                    } else {
                        $errors[] = "Sorry, there was an error uploading your file.";
                    }
                }
            } else {
                $errors[] = "File is not an image.";
                $uploadOk = 0;
            }
        }
        
        if (empty($errors)) {
            // Update database
            $sqlUpdate = "UPDATE cars SET name=?, price=?, description=?, model=?, transmission=?, interior=?, brand=?, image=?, quantity=? 
                          WHERE id=?";
            $updateStmt = $conn->prepare($sqlUpdate);
            $updateStmt->bind_param("sdssssssii", $name, $price, $description, $model, $transmission, $interior, $brand, $relativePath, $quantity, $carId);
            
            if ($updateStmt->execute()) {
                // Redirect to the manage cars page with success message
                header("Location: manage_cars.php?success=Car updated successfully");
                exit;
            } else {
                $errors[] = "Error: " . $updateStmt->error;
            }
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
            display: none; /* Hidden by default when editing (showing current image) */
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
                        <h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>Edit Car</h1>
                        <p class="text-muted">Update vehicle information in your fleet</p>
                    </div>
                    <div>
                        <a href="manage_cars.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Cars
                        </a>
                    </div>
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
                
                <!-- Edit Car Form -->
                <div class="form-card">
                    <div class="form-header">
                        <h5 class="mb-0">Car Details</h5>
                    </div>
                    <div class="form-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $carId); ?>" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-4 mb-4 mb-md-0">
                                    <div class="field-group">
                                        <label class="form-label d-block">Car Image</label>
                                        <div class="image-preview-container" id="imagePreviewContainer">
                                            <?php if(!empty($car['image']) && file_exists('../' . $car['image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" 
                                                     class="image-preview" id="imagePreview" style="display: block;">
                                            <?php else: ?>
                                                <img src="#" alt="Preview" class="image-preview" id="imagePreview" style="display: none;">
                                            <?php endif; ?>
                                            <div class="upload-placeholder" id="uploadPlaceholder" <?php echo (!empty($car['image']) && file_exists('../' . $car['image'])) ? 'style="display: none;"' : ''; ?>>
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <p>Click or drop image here</p>
                                            </div>
                                        </div>
                                        <input type="file" class="form-control mt-3" id="carImage" name="carImage" accept="image/*">
                                        <div class="form-hint">Recommended: 800×600 px, max 5MB. JPG, PNG formats only.</div>
                                    </div>
                                </div>
                                
                                <!-- Right Column -->
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="name" class="form-label">Car Name <span class="required-asterisk">*</span></label>
                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($car['name']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="brand" class="form-label">Brand <span class="required-asterisk">*</span></label>
                                                <input type="text" class="form-control" id="brand" name="brand" list="brandList" 
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
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="model" class="form-label">Model <span class="required-asterisk">*</span></label>
                                                <input type="text" class="form-control" id="model" name="model" 
                                                       value="<?php echo htmlspecialchars($car['model']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="price" class="form-label">Price per Day <span class="required-asterisk">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" 
                                                           value="<?php echo htmlspecialchars($car['price']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="quantity" class="form-label">Quantity Available <span class="required-asterisk">*</span></label>
                                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="<?php echo htmlspecialchars($car['quantity']); ?>" required>
                                                <div class="form-text">Number of cars of this model available for rent</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="transmission" class="form-label">Transmission</label>
                                                <select class="form-select" id="transmission" name="transmission">
                                                    <option value="Automatic" <?php echo $car['transmission'] === 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                                                    <option value="Manual" <?php echo $car['transmission'] === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                                                    <option value="Semi-Automatic" <?php echo $car['transmission'] === 'Semi-Automatic' ? 'selected' : ''; ?>>Semi-Automatic</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label for="interior" class="form-label">Interior</label>
                                                <select class="form-select" id="interior" name="interior">
                                                    <option value="Fabric" <?php echo $car['interior'] === 'Fabric' ? 'selected' : ''; ?>>Fabric</option>
                                                    <option value="Leather" <?php echo $car['interior'] === 'Leather' ? 'selected' : ''; ?>>Leather</option>
                                                    <option value="Sport" <?php echo $car['interior'] === 'Sport' ? 'selected' : ''; ?>>Sport</option>
                                                    <option value="Premium" <?php echo $car['interior'] === 'Premium' ? 'selected' : ''; ?>>Premium</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="field-group">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="5" 
                                                  placeholder="Enter car description, features, etc."><?php echo htmlspecialchars($car['description']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between">
                                <a href="delete_car.php?id=<?php echo $carId; ?>" class="btn btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this car? This action cannot be undone.')">
                                    <i class="fas fa-trash me-2"></i>Delete Car
                                </a>
                                
                                <div class="d-flex gap-2">
                                    <a href="manage_cars.php" class="btn btn-light px-4">Cancel</a>
                                    <button type="submit" class="admin-btn admin-btn-primary admin-btn-lg px-5">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </div>
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
        
        // Image preview functionality
        const imageInput = document.getElementById('carImage');
        const imagePreview = document.getElementById('imagePreview');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.src = reader.result;
                    imagePreview.style.display = 'block';
                    uploadPlaceholder.style.display = 'none';
                });
                
                reader.readAsDataURL(file);
            }
        });
        
        // Make the image container clickable to trigger file input
        const imageContainer = document.getElementById('imagePreviewContainer');
        imageContainer.addEventListener('click', function() {
            imageInput.click();
        });
        
        // Drag and drop functionality for image upload
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            imageContainer.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            imageContainer.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            imageContainer.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            imageContainer.style.borderColor = '#3182ce';
            imageContainer.style.backgroundColor = '#ebf8ff';
        }
        
        function unhighlight() {
            imageContainer.style.borderColor = '#cbd5e0';
            imageContainer.style.backgroundColor = '#f8fafc';
        }
        
        imageContainer.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                imageInput.files = files;
                const event = new Event('change');
                imageInput.dispatchEvent(event);
            }
        }
    </script>
</body>
</html>