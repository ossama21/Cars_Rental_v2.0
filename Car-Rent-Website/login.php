<?php
session_start();  // Start the session

// Check if the login form is submitted
if (isset($_POST['login'])) {
    
    // Get the input values from the form
    $emailid = $_POST['email'];
    $pass = $_POST['pass'];

    // Establish a connection to the database
    $con = mysqli_connect('127.0.0.1', 'root', '', 'car_rent');
    
    // Check for connection error
    if ($con == false) {
        die("Error in connection");
    } else {
        // Sanitize the inputs to prevent SQL Injection
        $emailid = mysqli_real_escape_string($con, $emailid);
        $pass = mysqli_real_escape_string($con, $pass);

        // Query to check if email and password exist in the database
        $select = "SELECT * FROM `customers` WHERE `emailid`='$emailid' AND `password`='$pass'";
        $query = mysqli_query($con, $select);

        // Check if exactly one user matches the email and password
        $row = mysqli_num_rows($query);
        if ($row == 1) {
            // Fetch user data
            $data = mysqli_fetch_assoc($query);
            
            // Store user data in session variables
            $_SESSION['name'] = $data['name'];  // Store the user's name in session

            // Redirect to the booking page with a success message
            ?>
            <script>
                alert("You have successfully logged in");
                window.open('mydashboard.php', '_self');  // Redirect to the dashboard page
            </script>
            <?php
        } else {
            // If no match, alert and redirect to login page
            ?>
            <script>
                alert("Wrong Email or Password! Please try again.");
                window.open('index.html', '_self');  // Redirect to the login page
            </script>
            <?php
        }
    }
}
?>
