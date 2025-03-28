<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the requested action from query string
$action = $_GET['action'] ?? 'login';

// Redirect to appropriate page
if ($action === 'login') {
    include 'login.php';
} else if ($action === 'register') {
    include 'register.php';
} else {
    // Invalid action, redirect to login
    header('Location: login.php');
    exit;
}
?>