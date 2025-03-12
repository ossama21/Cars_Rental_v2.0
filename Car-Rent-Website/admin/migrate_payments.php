<?php
include '../data/connect.php';

// Check if the payments table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'payments'");
if ($tableCheck->num_rows == 0) {
    // Create payments table if it doesn't exist
    $createTable = "CREATE TABLE `payments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `booking_id` int(11) NOT NULL,
        `method` varchar(50) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `status` enum('completed','pending','failed') DEFAULT 'completed',
        `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `transaction_id` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `booking_id` (`booking_id`),
        CONSTRAINT `payments_booking_fk` FOREIGN KEY (`booking_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($createTable)) {
        die("Error creating payments table: " . $conn->error);
    }
}

// Migrate existing payment data from services
$migrateQuery = "INSERT INTO payments (booking_id, method, amount, date)
                 SELECT id, payment_method, amount, created_at
                 FROM services
                 WHERE payment_method IS NOT NULL AND amount > 0
                 AND NOT EXISTS (
                     SELECT 1 FROM payments WHERE booking_id = services.id
                 )";

if ($conn->query($migrateQuery)) {
    echo "Payment data migration completed successfully!";
} else {
    echo "Error migrating payment data: " . $conn->error;
}
?>