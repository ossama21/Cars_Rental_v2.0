<?php
session_start();
include("connect.php");

if (!isset($_SESSION['id']) || !isset($_POST['method_id'])) {
    header("Location: my_account.php");
    exit();
}

$userId = $_SESSION['id'];
$methodId = intval($_POST['method_id']);

// Delete the payment method only if it belongs to the current user
$stmt = $conn->prepare("DELETE FROM saved_payment_methods WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $methodId, $userId);
$stmt->execute();

// Redirect back to homepage
header("Location: my_account.php");
exit();