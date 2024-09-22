<?php
session_start();
include '../data/connect.php';

// Admin check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch all payments
$sqlPayments = "SELECT * FROM payments";
$paymentsResult = $conn->query($sqlPayments);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
</head>
<body>

    <div class="container">
        <h2>Manage Payments</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($payment = $paymentsResult->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $payment['id']; ?></td>
                        <td><?= $payment['method']; ?></td>
                        <td><?= $payment['amount']; ?></td>
                        <td><?= $payment['date']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</body>
</html>
