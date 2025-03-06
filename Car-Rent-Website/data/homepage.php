<?php
session_start();
include("connect.php");

// Check if the user is logged in by checking the session variable
if (!isset($_SESSION['email'])) {
    // Redirect to the new login/signup page instead of login.php
    header("Location: index.php");
    exit();
}

// Retrieve the session variables
$email = $_SESSION['email'];
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : 'User';
$lastName = isset($_SESSION['lastName']) ? $_SESSION['lastName'] : '';
$age = isset($_SESSION['age']) ? $_SESSION['age'] : 'Not provided';
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : 'Not provided';
$address = isset($_SESSION['address']) ? $_SESSION['address'] : 'Not provided';
$visa = isset($_SESSION['visa']) && !empty($_SESSION['visa']) ? '****-****-****-' . substr($_SESSION['visa'], -4) : 'Not provided';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - CARSrent</title>
    <link rel="icon" type="image/png" href="../images/image.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #3182ce;
            padding: 1rem;
            color: white;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .profile-header h1 {
            color: #2d3748;
            margin: 0;
            font-size: 2rem;
        }

        .welcome-message {
            color: #718096;
            margin-top: 0.5rem;
        }

        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .info-card {
            background: #f7fafc;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .info-card h2 {
            color: #3182ce;
            font-size: 1.2rem;
            margin-top: 0;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .info-card h2 i {
            margin-right: 0.5rem;
        }

        .info-card p {
            color: #4a5568;
            margin: 0.5rem 0;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #3182ce;
            color: white;
        }

        .btn-secondary {
            background-color: #718096;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>CARSrent</h1>
    </div>

    <div class="container">
        <div class="profile-header">
            <h1>My Account</h1>
            <p class="welcome-message">Welcome back, <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?>!</p>
        </div>

        <div class="profile-info">
            <div class="info-card">
                <h2><i class="fas fa-user"></i>Personal Information</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($age); ?></p>
            </div>

            <div class="info-card">
                <h2><i class="fas fa-address-card"></i>Contact Details</h2>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>
            </div>

            <div class="info-card">
                <h2><i class="fas fa-credit-card"></i>Payment Information</h2>
                <p><strong>Visa Card:</strong> <?php echo htmlspecialchars($visa); ?></p>
            </div>
        </div>

        <div class="button-group">
            <a href="../index.php" class="btn btn-primary">Back to Home</a>
            <a href="logout.php" class="btn btn-secondary">Log Out</a>
        </div>
    </div>
</body>
</html>
