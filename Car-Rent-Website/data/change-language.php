<?php
session_start();

// Get language code from query parameter
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

// Validate language code (only allow specific languages)
$allowed_languages = ['en', 'fr', 'ar'];
if (!in_array($lang, $allowed_languages)) {
    $lang = 'en'; // Default to English if invalid language
}

// Set the language in session
$_SESSION['lang'] = $lang;

// Get the redirect URL or default to index page
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../index.php';

// Redirect back to the referring page
header('Location: ' . $redirect);
exit();