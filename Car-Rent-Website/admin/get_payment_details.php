<?php
session_start();
include '../data/connect.php';

// Admin check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Payment ID is required']);
    exit();
}

$paymentId = intval($_GET['id']);

$sql = "SELECT p.*, s.username as customer_name, s.email as customer_email, 
        s.start_date as booking_date, s.end_date as return_date, c.model,
        s.phone, s.duration, s.payment_details
        FROM payments p
        LEFT JOIN services s ON p.booking_id = s.id
        LEFT JOIN cars c ON s.car_id = c.id
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $paymentId);
$stmt->execute();
$result = $stmt->get_result();

if ($payment = $result->fetch_assoc()) {
    // Clean sensitive data and format dates
    $payment['date'] = date('Y-m-d H:i:s', strtotime($payment['date']));
    $payment['booking_date'] = date('Y-m-d', strtotime($payment['booking_date']));
    $payment['return_date'] = date('Y-m-d', strtotime($payment['return_date']));
    
    // Remove any sensitive information
    unset($payment['password']);
    
    header('Content-Type: application/json');
    echo json_encode($payment);
} else {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Payment not found']);
}
?>