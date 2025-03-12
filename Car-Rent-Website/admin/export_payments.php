<?php
session_start();
include '../data/connect.php';

// Admin check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch all payments data
$sql = "SELECT p.id, s.username as customer_name, s.email as customer_email, 
        c.model as car_model, p.method, p.amount, p.status, 
        s.start_date as booking_date, s.end_date as return_date, 
        p.date as payment_date, p.transaction_id
        FROM payments p
        LEFT JOIN services s ON p.booking_id = s.id
        LEFT JOIN cars c ON s.car_id = c.id
        ORDER BY p.date DESC";

$result = $conn->query($sql);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="payments_export_' . date('Y-m-d') . '.xls"');

// Print Excel header
echo "Payment ID\tCustomer Name\tEmail\tCar Model\tPayment Method\tAmount\tStatus\tBooking Date\tReturn Date\tPayment Date\tTransaction ID\n";

// Print data rows
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . "\t";
    echo $row['customer_name'] . "\t";
    echo $row['customer_email'] . "\t";
    echo $row['car_model'] . "\t";
    echo $row['method'] . "\t";
    echo $row['amount'] . "\t";
    echo ($row['status'] ?? 'completed') . "\t";
    echo date('Y-m-d', strtotime($row['booking_date'])) . "\t";
    echo date('Y-m-d', strtotime($row['return_date'])) . "\t";
    echo date('Y-m-d H:i:s', strtotime($row['payment_date'])) . "\t";
    echo ($row['transaction_id'] ?? 'N/A') . "\n";
}
?>