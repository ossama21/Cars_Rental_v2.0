<?php
session_start();
include '../data/connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT role FROM users WHERE email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Redirect if not admin
if ($user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle car management actions here (add, update, delete cars)

// Fetch all cars from the database
$sqlCars = "SELECT * FROM cars";
$carsResult = $conn->query($sqlCars);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
</head>
<body>

    <div class="container">
        <h2 class="mt-5">Manage Cars</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Model</th>
                    <th>Transmission</th>
                    <th>Interior</th>
                    <th>Brand</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($car = $carsResult->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $car['id']; ?></td>
                        <td><?= $car['name']; ?></td>
                        <td><?= $car['price']; ?></td>
                        <td><?= $car['description']; ?></td>
                        <td><?= $car['model']; ?></td>
                        <td><?= $car['transmission']; ?></td>
                        <td><?= $car['interior']; ?></td>
                        <td><?= $car['brand']; ?></td>
                        <td>
                            <a href="edit_car.php?id=<?= $car['id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="delete_car.php?id=<?= $car['id']; ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
