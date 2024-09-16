<?php 

include 'connect.php';

if(isset($_POST['signUp'])){
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password); // Note: Consider using password_hash() for better security.

    // Check if the email already exists
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);
    
    if($result->num_rows > 0){
        echo "Email Address Already Exists!";
    } else {
        // Insert new user
        $insertQuery = "INSERT INTO users (firstName, lastName, email, password)
                        VALUES ('$firstName', '$lastName', '$email', '$password')";
        if($conn->query($insertQuery) === TRUE){
            header("Location: index.php");
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

if(isset($_POST['signIn'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password); // Again, consider password_hash() and password_verify()

    // Query to check login credentials
    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0){
        session_start();
        $row = $result->fetch_assoc();
        
        // Store the user's first name and email in the session
        $_SESSION['email'] = $row['email'];
        $_SESSION['firstName'] = $row['firstName']; // Fetching first name
        
        header("Location: homepage.php");
        exit();
    } else {
        echo "Not Found, Incorrect Email or Password";
    }
}
?>
