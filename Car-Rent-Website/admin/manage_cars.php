<?php
session_start();
include '../data/connect.php';

if (!isset($_SESSION['email'])) {
    header('Location: ../index.php');
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT role FROM users WHERE email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Uncomment this if you want to restrict access based on user role
// if ($user['role'] !== 'admin') {
//     header('Location: ../index.php');
//     exit();
// }

$sqlCars = "SELECT * FROM cars";
$carsResult = $conn->query($sqlCars);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h2 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        .top-buttons {
            margin-bottom: 30px;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            background-color: #0d6efd;
            color: #ffffff;
            font-weight: 500;
            text-transform: uppercase;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 0.875rem;
        }
        .btn-edit {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        .btn-delete {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top-buttons d-flex justify-content-between align-items-center">
        <a href="../admin/admin.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Admin</a>
        <a href="add_car.php" class="btn btn-success"><i class="fas fa-plus me-2"></i>Add New Car</a>
    </div>

    <h2>Manage Cars</h2>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
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
                        <td><?= $car['price']; ?> $/day</td>
                        <td><?= substr($car['description'], 0, 50) . '...'; ?></td>
                        <td><?= $car['model']; ?></td>
                        <td><?= $car['transmission']; ?></td>
                        <td><?= $car['interior']; ?></td>
                        <td><?= $car['brand']; ?></td>
                        <td>
                            <a href="edit_car.php?id=<?= $car['id']; ?>" class="btn btn-action btn-edit"><i class="fas fa-edit me-1"></i>Edit</a>
                            <a href="delete_car.php?id=<?= $car['id']; ?>" class="btn btn-action btn-delete" onclick="return confirmDelete(event);"> <i class="fas fa-trash me-1"></i>Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete(event) {
        if (!confirm("Do you really want to delete this car?")) {
            event.preventDefault();  // Stop the navigation if the user cancels
        }
    }
</script>

</body>
</html>