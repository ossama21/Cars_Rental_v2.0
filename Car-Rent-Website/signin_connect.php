<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();  // Start the session

if (isset($_POST['login'])) {
    $username = $_POST['username'];  // Fetch username from POST data
    $password = $_POST['password'];  // Fetch password from POST data

    // Check if the username and password are provided
    if (empty($username) || empty($password)) {
        echo "Please fill in both username and password.";
        exit();
    }

    // Connect to the database
    $con = new mysqli('127.0.0.1', 'root', '', 'car_rent');

    // Check the connection
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $con->prepare("SELECT `password`, `name` FROM `customers` WHERE `username` = ?");
    $stmt->bind_param("s", $username);  // Bind username instead of emailid
    $stmt->execute();
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($hashedPassword, $name);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['name'] = $name;
            echo "Login successful! Redirecting to dashboard...";
            header("Location: mydashboard.php");
            exit();  // Always use exit after header redirection
        } else {
            echo "Incorrect password. Please try again.";
        }
    } else {
        echo "No user found with this username.";
    }

    // Close the statement and connection
    $stmt->close();
    $con->close();
}
?>
