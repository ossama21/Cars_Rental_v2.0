<?php

// Handle car addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-car'])) {
    $carName = $_POST['car-name'];
    $carPrice = $_POST['car-price'];
    $carDescription = $_POST['car-description'];
    
    // Image upload logic (limited to specific resolution)
    if (isset($_FILES['car-image']) && $_FILES['car-image']['error'] === 0) {
        $imageFile = $_FILES['car-image'];
        $imagePath = 'car_images/' . basename($imageFile['name']);
        move_uploaded_file($imageFile['tmp_name'], $imagePath);
    }
    
    // Add the new car to your database (this is just a placeholder)
    // INSERT INTO cars (name, price, description, image) VALUES ($carName, $carPrice, $carDescription, $imagePath);
}

// Handle car deletion
if (isset($_GET['delete'])) {
    $carId = $_GET['delete'];
    // DELETE FROM cars WHERE id = $carId;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Manage Cars</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Admin Panel - Manage Cars</h1>
        
        <div class="add-car-section">
            <h2>Add a New Car</h2>
            <form action="admin.php" method="POST" enctype="multipart/form-data">
                <label for="car-name">Car Name:</label>
                <input type="text" id="car-name" name="car-name" required>
                
                <label for="car-price">Price:</label>
                <input type="number" id="car-price" name="car-price" required>
                
                <label for="car-description">Description:</label>
                <textarea id="car-description" name="car-description" required></textarea>
                
                <label for="car-image">Car Image (Resolution: 800x600px):</label>
                <input type="file" id="car-image" name="car-image" accept="image/*" required>
                
                <button type="submit" name="add-car" class="blue-btn">Add Car</button>
            </form>
        </div>

        <hr>

        <div class="car-list-section">
            <h2>Manage Existing Cars</h2>
            <div class="car-list">
                <?php foreach ($cars as $car) { ?>
                <div class="car-item">
                    <img src="<?php echo $car['image']; ?>" alt="<?php echo $car['name']; ?>" width="200" height="150">
                    <h3><?php echo $car['name']; ?></h3>
                    <p>Price: $<?php echo $car['price']; ?></p>
                    <p><?php echo $car['description']; ?></p>
                    <a href="admin.php?delete=<?php echo $car['id']; ?>" class="delete-btn">Delete</a>
                    <a href="edit.php?id=<?php echo $car['id']; ?>" class="edit-btn">Edit</a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script type="module" src="products.js"></script>
</body>
</html>
