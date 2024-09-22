<?php
session_start();
include '../data/connect.php';

// Admin check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Check if the user ID is set in the URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Delete user query
    $deleteSql = "DELETE FROM users WHERE id = $userId";

    if ($conn->query($deleteSql) === TRUE) {
        header('Location: manage_users.php');
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header('Location: manage_users.php');
    exit();
}
?>
