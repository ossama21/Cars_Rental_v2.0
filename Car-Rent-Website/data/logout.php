<?php
session_start(); // Start the session

// Destroy the session
session_destroy();

// Start a new session to set the session variable
session_start();

// Set a link as the session variable
$_SESSION['firstName'] = "";

// Redirect to index.php
header("location: ./index.php");
exit();
?>
