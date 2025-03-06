<?php
function checkAdminAccess() {
    if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../index.php');
        exit();
    }
}

function checkUserLogin() {
    if (!isset($_SESSION['email'])) {
        header('Location: ./data/login.php');
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['email']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>