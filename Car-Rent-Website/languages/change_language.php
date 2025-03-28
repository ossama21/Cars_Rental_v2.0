<?php
session_start();

// Check if language is set in the URL
if(isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    
    // Validate lang parameter (only allow specific languages)
    if($lang === 'en' || $lang === 'fr' || $lang === 'ar') {
        $_SESSION['lang'] = $lang;
    }
}

// Redirect back to the referring page or to index page if not set
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';
header("Location: $redirect");
exit;
?>