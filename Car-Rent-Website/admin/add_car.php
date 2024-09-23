<?php
session_start();
include '../data/connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

if (isset($_POST['addCar'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $model = $_POST['model'];
    $transmission = $_POST['transmission'];
    $interior = $_POST['interior'];
    $brand = $_POST['brand'];

    // Handle image upload
    $targetDir = "./images/"; // Make sure this folder exists
    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $targetFile = $targetDir . basename($_FILES["image"]["name"]); // Full path to store the image

    // Specify image resolution constraints
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    
    if (in_array($imageFileType, $allowedTypes) && $_FILES["image"]["size"] < 3000000) { // 3MB limit
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
        
        $sql = "INSERT INTO cars (name, price, description, model, transmission, interior, brand, image)
                VALUES ('$name', '$price', '$description', '$model', '$transmission', '$interior', '$brand', '$targetFile')";
        if ($conn->query($sql) === TRUE) {
            header("Location: manage_cars.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Invalid file type or size too large!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
</head>
<body>

<div class="container mt-5">
    <h2>Add New Car</h2>
    <a href="manage_cars.php" class="btn btn-secondary mb-3">Back to Manage Cars</a>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Car Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" name="price" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="model">Model</label>
            <input type="text" name="model" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="transmission">Transmission</label>
            <input type="text" name="transmission" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="interior">Interior</label>
            <input type="text" name="interior" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" name="brand" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="image">Car Image (max 2MB, jpg/jpeg/png)</label>
            <input type="file" name="image" class="form-control-file" required>
        </div>
        <button type="submit" name="addCar" class="btn btn-primary">Add Car</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
