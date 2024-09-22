<?php
session_start();
include '../data/connect.php';

if (isset($_GET['id'])) {
    $carId = $_GET['id'];
    
    $deleteQuery = "DELETE FROM cars WHERE id=$carId";
    
    if ($conn->query($deleteQuery) === TRUE) {
        header("Location: manage_cars.php");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>