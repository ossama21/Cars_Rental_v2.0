<?php
session_start();
include '../data/connect.php';

if (isset($_GET['id'])) {
    $carId = $_GET['id'];
    $carQuery = "SELECT * FROM cars WHERE id=$carId";
    $carResult = $conn->query($carQuery);
    $car = $carResult->fetch_assoc();

    if (isset($_POST['updateCar'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $model = $_POST['model'];
        $transmission = $_POST['transmission'];
        $interior = $_POST['interior'];
        $brand = $_POST['brand'];

        // Prepare the update query without the image first
        $updateQuery = "UPDATE cars SET 
            name='$name', price='$price', description='$description',
            model='$model', transmission='$transmission',
            interior='$interior', brand='$brand'
            WHERE id=$carId";

        // Check if a new image has been uploaded
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "../images/"; // Directory to store images
            $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $targetFile = $targetDir . basename($_FILES["image"]["name"]);

            // Allowed file types and size constraint (3MB limit)
            $allowedTypes = ['jpg', 'jpeg', 'png'];

            if (in_array($imageFileType, $allowedTypes) && $_FILES["image"]["size"] < 3000000) {
                // Move the uploaded file
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    // Update query including the new image
                    $imagePath = "images/" . basename($_FILES["image"]["name"]); // Save relative path
                    $updateQuery = "UPDATE cars SET 
                        name='$name', price='$price', description='$description',
                        model='$model', transmission='$transmission',
                        interior='$interior', brand='$brand', image='$imagePath'
                        WHERE id=$carId";
                } else {
                    echo "Error uploading the file.";
                }
            } else {
                echo "Invalid file type or size too large!";
            }
        }

        // Execute the update query
        if ($conn->query($updateQuery) === TRUE) {
            header("Location: manage_cars.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h2 {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            border-color: #0d6efd;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #565e64;
        }
        .image-preview {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2><i class="fas fa-car me-2"></i>Edit Car Details</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Car Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($car['name']) ?>" placeholder="Enter Car Name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Car Price ($/day)</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($car['price']) ?>" placeholder="Enter Car Price" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Car Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter Car Description"><?= htmlspecialchars($car['description']) ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="model" class="form-label">Car Model</label>
                    <input type="text" class="form-control" id="model" name="model" value="<?= htmlspecialchars($car['model']) ?>" placeholder="Enter Car Model" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="transmission" class="form-label">Transmission</label>
                    <input type="text" class="form-control" id="transmission" name="transmission" value="<?= htmlspecialchars($car['transmission']) ?>" placeholder="Enter Transmission Type" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="interior" class="form-label">Interior</label>
                    <input type="text" class="form-control" id="interior" name="interior" value="<?= htmlspecialchars($car['interior']) ?>" placeholder="Enter Interior Type" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" class="form-control" id="brand" name="brand" value="<?= htmlspecialchars($car['brand']) ?>" placeholder="Enter Brand Name" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Car Image (Recommended: 600x400)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>
            <?php if (!empty($car['image'])): ?>
                <div class="mb-3">
                    <label class="form-label">Current Image</label>
                    <img src="../<?= htmlspecialchars($car['image']) ?>" alt="Current car image" class="image-preview">
                </div>
            <?php endif; ?>
            <div class="d-grid gap-2">
                <button type="submit" name="updateCar" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Car</button>
                <a href="manage_cars.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Manage Cars</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>