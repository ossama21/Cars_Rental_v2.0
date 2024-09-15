<?php
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $emailid = $_POST['email'];
    $username = $_POST['username'];
    $pass = $_POST['pass'];
    $repeatpass = $_POST['repeatpass'];

    // Database connection
    $con = mysqli_connect('127.0.0.1', 'root', '', 'car_rent');
    if ($con == false) {
        echo "Error in database connection!!";
    } else {
        // Check for empty fields
        if (empty($name) || empty($emailid) || empty($username) || empty($pass) || empty($repeatpass)) {
            echo "<script>alert('All fields are required!'); window.open('Register.html', '_self');</script>";
            exit;
        }

        // Check if either the email or username already exists
        $stmt = $con->prepare("SELECT * FROM `customers` WHERE `emailid` = ? OR `username` = ?");
        $stmt->bind_param("ss", $emailid, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $num = $result->num_rows;

        if ($num > 0) {
            echo "<script>alert('Email or Username already exists! Please use a different email or username.'); window.open('Register.html', '_self');</script>";
            exit;
        } else {
            // Insert the new record
            $stmt = $con->prepare("INSERT INTO `customers`(`name`, `emailid`, `username`, `password`, `repeatpassword`) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $emailid, $username, $pass, $repeatpass);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script>alert('Registered Successfully'); window.open('index.html', '_self');</script>";
            } else {
                echo "Error in registration";
            }
        }
    }
}
?>
