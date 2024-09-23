<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $username = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $startDate = htmlspecialchars($_POST['startDate']);
    $endDate = htmlspecialchars($_POST['endDate']);
    $email = htmlspecialchars($_POST['email']);

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
    $insertQuery = $con->prepare("INSERT INTO `services` (`username`, `phone`, `start_date`, `end_date`, `duration`,`email`) VALUES (?, ?, ?, ?, ?,?)");
    $insertQuery->bind_param('ssssss', $username, $phone, $startDate, $endDate, $duration, $email);


    if ($insertQuery->execute()) {
        // Store data in session for use in other pages
        $_SESSION['username'] = $username;
        $_SESSION['phone'] = $phone;
        $_SESSION['startDate'] = $startDate;
        $_SESSION['endDate'] = $endDate;
        $_SESSION['duration'] = $duration;
        $_SESSION['email'] = $email;
    } else {
        echo "Error: " . $insertQuery->error;
    }

    // Close the database connection
    $insertQuery->close();
    mysqli_close($con);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./images/image.png">

    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            margin: 0;
            padding: 0 0 10px;
            border-bottom: 2px solid #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f9f9f9;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .invoice-header h1 {
            margin: 0;
            font-size: 30px;
        }
        .invoice-header a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="invoice-header">
            <a href="#">CARSRENT</a>
            <h1>INVOICE</h1>
        </div>
        <table>
            <tbody>
                <tr>
                    <td><strong>Your Car Waiting for You:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></td>
                    <td align="right"><strong>Date Issued:</strong> <?php echo htmlspecialchars($_SESSION['startDate']); ?></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div><strong>Bill To:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div><strong>Bill From:</strong> Cars Rent</div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th>start Date</th>
                    <th>End Date</th>
                    <th>Duration (days)</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($_SESSION['startDate']); ?></td>
                    <td><?php echo htmlspecialchars($_SESSION['endDate']); ?></td>
                    <td><?php echo htmlspecialchars($_SESSION['duration']); ?></td>
                    <td><?php echo htmlspecialchars($_SESSION['phone']); ?></td>
                </tr>
            </tbody>
        </table>
        <a href="index.php" class="button">Go to Home Page</a>
        <a href="generate_invoice.php" class="button" target="_blank">Download Invoice as PDF</a>
    </div>
</body>
</html>

