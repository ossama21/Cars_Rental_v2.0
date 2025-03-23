<?php
include '../data/connect.php';

// Create the index_cars directory if it doesn't exist
$indexCarsDir = '../images/cars/index_cars';
if (!file_exists($indexCarsDir)) {
    mkdir($indexCarsDir, 0777, true);
}

// Get all cars with their primary images
$sql = 'SELECT c.id, ci.image_path, c.image 
        FROM cars c 
        LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_primary = 1';
$result = $conn->query($sql);

while ($car = $result->fetch_assoc()) {
    // Try primary image first
    if (!empty($car['image_path']) && file_exists('../' . $car['image_path'])) {
        $srcPath = '../' . $car['image_path'];
        $destPath = $indexCarsDir . '/' . $car['id'] . '.jpg';
        copy($srcPath, $destPath);
        echo 'Copied primary image for car ID ' . $car['id'] . PHP_EOL;
    }
    // Fall back to legacy image if exists
    else if (!empty($car['image']) && file_exists('../' . $car['image'])) {
        $srcPath = '../' . $car['image'];
        $destPath = $indexCarsDir . '/' . $car['id'] . '.jpg';
        copy($srcPath, $destPath);
        echo 'Copied legacy image for car ID ' . $car['id'] . PHP_EOL;
    }
}

echo 'Index car images setup completed!';
?>
