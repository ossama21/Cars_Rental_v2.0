<?php
// get_products.php
include 'connect.php'; // Adjust the path if necessary

// Fetch all cars from the database
$sql = "SELECT * FROM cars";
$result = $conn->query($sql);

// Prepare an array to hold the car data
$cars = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add each car to the cars array
        $cars[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'description' => $row['description'],
            'model' => $row['model'],
            'transmission' => $row['transmission'],
            'interior' => $row['interior'],
            'brand' => $row['brand'],
            'image' => $row['image'] // Assuming the image URL is stored in the database
        ];
    }
}

// Return the cars as a JSON object
header('Content-Type: application/json');
echo json_encode($cars);

$conn->close();
?>
