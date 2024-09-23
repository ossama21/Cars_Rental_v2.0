<?php
session_start();
include("connect.php");

// Check if the user is logged in by checking the session variable
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve the session email and first name
$email = $_SESSION['email'];
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : 'User'; // Fallback to 'User' if not available
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./images/image.png">

    <title>Homepage</title>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f0f0f0;
            text-align: center;
            padding: 10%;
        }

        .welcome-message {
            font-size: 50px;
            font-weight: bold;
            color: #333;
        }

        .email-info {
            font-size: 24px;
            margin-top: 20px;
            color: #555;
        }

        .logout-btn {
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #3182ce;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 20px;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background-color: #333;
        }

        a {
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>

    <div class="welcome-message">
        Hello, <?php echo htmlspecialchars($firstName); ?>! Welcome to CARrent
    </div>

    <div class="email-info">
        Your email: <?php echo htmlspecialchars($email); ?>
    </div>

    <form method="POST" action="logout.php">
        <button type="submit" class="logout-btn">Log Out</button>
    </form>

    <button type="submit" class="logout-btn">
        <a href="../index.php">Home</a>
    </button>

</body>
</html>
