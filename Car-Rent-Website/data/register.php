<?php
session_start();
include 'connect.php';

if (isset($_POST['signUp'])) {
    $fName = trim($_POST['fName']);
    $lName = trim($_POST['lName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match!";
        header('Location: index.php');
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email already registered!";
        header('Location: index.php');
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user without visa field
    $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password, age, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'user')");
    $stmt->bind_param("sssssss", $fName, $lName, $email, $hashedPassword, $age, $phone, $address);
    
    if ($stmt->execute()) {
        $_SESSION['id'] = $stmt->insert_id;
        $_SESSION['firstName'] = $fName;
        $_SESSION['lastName'] = $lName;
        $_SESSION['email'] = $email;
        $_SESSION['age'] = $age;
        $_SESSION['phone'] = $phone;
        $_SESSION['address'] = $address;
        $_SESSION['role'] = 'user';
        header('Location: ../index.php');
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header('Location: index.php');
        exit();
    }
}

if (isset($_POST['signIn'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['firstName'] = $user['firstName'];
            $_SESSION['lastName'] = $user['lastName'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['age'] = $user['age'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: ../admin/admin.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        }
    }
    
    $_SESSION['error'] = "Invalid email or password!";
    header('Location: index.php');
    exit();
}

header('Location: index.php');
?>
