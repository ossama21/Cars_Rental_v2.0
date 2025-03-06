<?php
session_start(); // Start the session

// Clear all session variables
$_SESSION = array();

// If a session cookie is used, delete it
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destroy the session
session_destroy();

// Redirect to main index.php (homepage)
header("location: ../index.php");
exit();
?>
