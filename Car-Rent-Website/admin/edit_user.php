<?php
session_start();
include '../data/connect.php';

// Admin check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get user ID from the URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user details from the database
    $sql = "SELECT * FROM users WHERE id = $userId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found!";
        exit();
    }
} else {
    header('Location: manage_users.php');
    exit();
}

// Handle form submission
if (isset($_POST['updateUser'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Update user information in the database
    $updateSql = "UPDATE users SET firstName='$firstName', lastName='$lastName', email='$email', role='$role' WHERE id=$userId";

    if ($conn->query($updateSql) === TRUE) {
        header('Location: manage_users.php');
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit User</h2>
        <form method="POST">
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" class="form-control" name="firstName" value="<?= $user['firstName']; ?>" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" class="form-control" name="lastName" value="<?= $user['lastName']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" value="<?= $user['email']; ?>" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select class="form-control" name="role" required>
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <button type="submit" name="updateUser" class="btn btn-primary">Update User</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
