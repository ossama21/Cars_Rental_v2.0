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
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .admin-header {
            margin-top: 50px;
            text-align: center;
            color: #343a40;
        }
        .admin-section {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        .admin-card {
            width: 280px;
            height: 180px;
            margin: 15px;
            background-color: #007bff;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .admin-card:hover {
            background-color: #0056b3;
            transform: translateY(-5px);
        }
        .admin-card a {
            text-decoration: none;
            color: white;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .admin-card i {
            font-size: 30px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container admin-header">
    <h1>Admin Dashboard</h1>
    <p class="lead">Welcome, Admin! Manage your application efficiently from here.</p>
</div>

<div class="container admin-section">
    <div class="admin-card">
        <i class="fas fa-car"></i>
        <a href="manage_cars.php">Manage Cars</a>
    </div>
    <div class="admin-card">
        <i class="fas fa-users"></i>
        <a href="manage_users.php">Manage Users</a>
    </div>
    <div class="admin-card">
        <i class="fas fa-dollar-sign"></i>
        <a href="manage_payments.php">Manage Payments</a>
    </div>
</div>

</body>
</html>
