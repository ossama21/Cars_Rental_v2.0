<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $username = $_POST['name'];
    $phone = $_POST['phone'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $quantity = $_POST['quantity'];

    // Calculate the duration (days) of the booking
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    $duration = $startDateObj->diff($endDateObj)->days;

    // Connect to the database
    $con = mysqli_connect('127.0.0.1', 'root', '', 'car_rent');

    if (!$con) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // Insert the booking information into the database
    $insertQuery = "INSERT INTO `services` (`username`, `phone`, `start_date`, `end_date`, `quantity`, `duration`) 
                    VALUES ('$username', '$phone', '$startDate', '$endDate', '$quantity', '$duration')";

    if (mysqli_query($con, $insertQuery)) {
        // Success message and redirect
    

        // Store data in session for use in other pages
        $_SESSION['username'] = $username;
        $_SESSION['phone'] = $phone;
        $_SESSION['startDate'] = $startDate;
        $_SESSION['endDate'] = $endDate;
        $_SESSION['quantity'] = $quantity;
        $_SESSION['duration'] = $duration;
    } else {
        echo "Error: " . $insertQuery . "<br>" . mysqli_error($con);
    }

    // Close the database connection
    mysqli_close($con);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Bill</title>
    <link rel="icon" type="image/png" href="./images/image.png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #3182ce, #63b3ed);
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #fff;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
            background-color: rgba(49, 130, 206, 0.9);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            width: 80%;
            font-size: 2rem;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 20px auto;
            width: 80%;
            max-width: 800px;
        }

        .content {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 15px;
            width: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: #fff;
            font-size: 1.1rem;
            text-align: left;
        }

        .content h2 {
            margin-top: 0;
            color: #fff;
            font-size: 1.5rem;
        }

        .content p {
            margin: 10px 0;
        }

        .button {
            background: linear-gradient(45deg, #3182ce, #63b3ed);
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 1.2rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            margin: 20px auto;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .button:hover {
            background: linear-gradient(45deg, #63b3ed, #3182ce);
            transform: scale(1.05);
        }

        a {
            text-align: center;
            display: block;
        }

        @media (max-width: 768px) {
            .content {
                padding: 15px;
                width: 95%;
            }

            h1 {
                font-size: 1.5rem;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <h1>Thank You For Using Our Services</h1>
    
    <div class="container">
        <div class="content">
            <h2>Rental Details</h2>
            <?php
            echo "<p><strong>User Name:</strong> " . htmlspecialchars($_SESSION['username']) . "</p>";
            echo "<p><strong>Phone:</strong> " . htmlspecialchars($_SESSION['phone']) . "</p>";
            echo "<p><strong>Start Date:</strong> " . htmlspecialchars($_SESSION['startDate']) . "</p>";
            echo "<p><strong>End Date:</strong> " . htmlspecialchars($_SESSION['endDate']) . "</p>";
            echo "<p><strong>Quantity:</strong> " . htmlspecialchars($_SESSION['quantity']) . "</p>";
            echo "<p><strong>Duration:</strong> " . htmlspecialchars($_SESSION['duration']) . " DAYS</p>";
            ?>
        </div>

        <h3>Go to home page</h3>
        <a href="index.php">
            <button class="button">Click Here</button>
        </a>
    </div>
</body>
</html>
