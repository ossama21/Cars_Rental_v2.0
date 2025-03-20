<?php
include '../data/connect.php';

// Create the cars directory if it doesn't exist
$carsImageDir = "../images/cars";
if (!file_exists($carsImageDir)) {
    mkdir($carsImageDir, 0777, true);
}

// Execute the SQL from car_images.sql
$sql = file_get_contents('car_images.sql');
if ($conn->multi_query($sql)) {
    do {
        // Store the result and free it
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
}

// Get all cars
$cars = $conn->query("SELECT id, image FROM cars WHERE image IS NOT NULL AND image != ''");

// Move existing images to new structure
while ($car = $cars->fetch_assoc()) {
    $carId = $car['id'];
    $oldImagePath = "../" . $car['image'];
    
    // Create car-specific directory
    $carDir = "{$carsImageDir}/{$carId}";
    if (!file_exists($carDir)) {
        mkdir($carDir, 0777, true);
    }
    
    // Only process if the old image exists
    if (file_exists($oldImagePath)) {
        // Generate new filename
        $extension = pathinfo($oldImagePath, PATHINFO_EXTENSION);
        $newFileName = uniqid() . '.' . $extension;
        $newPath = "images/cars/{$carId}/{$newFileName}";
        $newFullPath = "../{$newPath}";
        
        // Copy the file to new location
        if (copy($oldImagePath, $newFullPath)) {
            // Insert into car_images table
            $stmt = $conn->prepare("INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, 1)");
            $stmt->bind_param("is", $carId, $newPath);
            $stmt->execute();
        }
    }
}

echo "Migration completed successfully!";
?>