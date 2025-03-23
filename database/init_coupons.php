<?php
include '../Car-Rent-Website/data/connect.php';

// Read and execute the SQL file
$sql = file_get_contents(__DIR__ . '/create_coupon_table.sql');

if ($conn->multi_query($sql)) {
    echo "Coupons table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>