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
        echo "<script>
                alert('Booking Registered Successfully');
                window.location.href='details.php';
              </script>";

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
