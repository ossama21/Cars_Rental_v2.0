<?php
session_start();
include '../data/connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['email'])) {
    header('Location: ../index.php');
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT role FROM users WHERE email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Redirect if not admin
// if ($user['role'] !== 'admin') {
//     header('Location: ../index.php');
//     exit();
// }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .admin-header {
            margin-top: 30px;
            text-align: center;
        }
        .admin-section {
            display: flex;
            justify-content: space-around;
            margin-top: 50px;
        }
        .admin-card {
            width: 300px;
            height: 150px;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: 0.3s;
        }
        .admin-card:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .admin-card a {
            text-decoration: none;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container admin-header">
    <h2>Admin Dashboard</h2>
</div>

<div class="container admin-section">
    <div class="admin-card">
        <a href="manage_cars.php">Manage Cars</a>
    </div>
    <div class="admin-card">
        <a href="manage_users.php">Manage Users</a>
    </div>
    <div class="admin-card">
        <a href="manage_payments.php">Manage Payments</a>
    </div>
</div>

</body>
</html>
