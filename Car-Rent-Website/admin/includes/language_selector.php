<?php
// Language selection handling
$availableLangs = ['en', 'fr', 'ar'];
$lang_code = isset($_SESSION['lang']) && in_array($_SESSION['lang'], $availableLangs) ? $_SESSION['lang'] : 'en';

// Set html direction for Arabic
$dir = $lang_code === 'ar' ? 'rtl' : 'ltr';

// Include the selected language file
include_once "../languages/{$lang_code}.php";
?>