<?php
// Database connection parameters
$host = "localhost";
$user = "root";
$pass = "";
$db = "car_rent";

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($db);

// Create services table with all required fields
$sql = "CREATE TABLE IF NOT EXISTS `services` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(255) NOT NULL,
    `phone` varchar(50) NOT NULL,
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `duration` int(11) NOT NULL,
    `email` varchar(255) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `car_id` int(11) NOT NULL,
    `payment_method` varchar(50) NOT NULL,
    `payment_details` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `car_id` (`car_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Services table created successfully or already exists<br>";
} else {
    echo "Error creating services table: " . $conn->error . "<br>";
}

// Create cars table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `cars` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `brand` varchar(100) NOT NULL,
    `model` varchar(100) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `transmission` varchar(50) NOT NULL,
    `interior` varchar(50) DEFAULT 'Standard',
    `description` text,
    `image` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Cars table created successfully or already exists<br>";
} else {
    echo "Error creating cars table: " . $conn->error . "<br>";
}

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `booking_id` int(11) NOT NULL,
    `method` varchar(50) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `status` enum('completed','pending','failed') DEFAULT 'completed',
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `transaction_id` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `booking_id` (`booking_id`),
    CONSTRAINT `payments_booking_fk` FOREIGN KEY (`booking_id`) 
    REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Payments table created successfully<br>";
} else {
    echo "Error creating payments table: " . $conn->error . "<br>";
}

// Add foreign key if it doesn't exist
$sql = "SELECT COUNT(1) FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_NAME = 'services_car_fk'";
$result = $conn->query($sql);
$exists = ($result->fetch_row()[0] > 0);

if (!$exists) {
    $sql = "ALTER TABLE `services` 
            ADD CONSTRAINT `services_car_fk` 
            FOREIGN KEY (`car_id`) 
            REFERENCES `cars` (`id`) 
            ON DELETE CASCADE 
            ON UPDATE CASCADE";
    
    if ($conn->query($sql) === TRUE) {
        echo "Foreign key constraint added successfully<br>";
    } else {
        echo "Error adding foreign key constraint: " . $conn->error . "<br>";
    }
}

$conn->close();
echo "Database initialization completed.";
?>