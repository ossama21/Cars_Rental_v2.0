<?php
session_start();
include '../data/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

// Fetch the current user's role from the database
$email = $_SESSION['email'];
$sql = "SELECT role FROM users WHERE email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Redirect non-admin users to homepage
if ($user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
    
    <!-- Custom CSS -->
    <style>
        /* Admin Panel Styles */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .admin-header {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .admin-container {
            margin-top: 30px;
        }
        .admin-sidebar {
            background-color: #444;
            color: white;
            padding: 15px;
        }
        .admin-sidebar a {
            color: #ddd;
            text-decoration: none;
            padding: 10px;
            display: block;
            transition: 0.3s;
        }
        .admin-sidebar a:hover {
            background-color: #555;
        }
        .admin-content {
            padding: 20px;
            background-color: white;
            margin-left: 220px; /* Adjust for sidebar */
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-content {
                margin-left: 0;
            }
            .admin-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="admin-header">
        <h1>Welcome to Admin Panel, <?= $_SESSION['firstName']; ?></h1>
    </div>

    <div class="container-fluid admin-container">
        <div class="row">
            <!-- Admin Sidebar -->
            <div class="col-md-3 admin-sidebar">
                <h3>Admin Menu</h3>
                <a href="manage_cars.php">Manage Cars</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="manage_payments.php">Manage Payments</a>
                <a href="../data/logout.php">Log Out</a>
            </div>

            <!-- Admin Content -->
            <div class="col-md-9 admin-content">
                <h2>Dashboard</h2>
                <p>Here you can manage cars, users, and payments for the car rental system.</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
