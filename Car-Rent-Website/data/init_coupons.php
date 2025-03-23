<?php
include 'connect.php';

// First check if the table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'coupons'")->num_rows > 0;

if (!$tableExists) {
    // Create table if it doesn't exist
    $sql = "CREATE TABLE coupons (
        id INT PRIMARY KEY AUTO_INCREMENT,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
        value DECIMAL(10,2) NOT NULL,
        min_rental_days INT NOT NULL DEFAULT 1,
        usage_limit INT DEFAULT NULL,
        times_used INT NOT NULL DEFAULT 0,
        start_date DATE NOT NULL,
        expiry_date DATE NOT NULL,
        description TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_code (code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($conn->query($sql)) {
        echo "Coupons table created successfully\n";
    } else {
        echo "Error creating coupons table: " . $conn->error . "\n";
        exit;
    }
} else {
    // Get current columns
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM coupons");
    while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = $row;
    }

    // Alter existing table to match our structure
    $alterQueries = [];

    // Modify code column if it exists
    if (isset($columns['code'])) {
        $alterQueries[] = "ALTER TABLE coupons MODIFY COLUMN code VARCHAR(50) NOT NULL";
    } else {
        $alterQueries[] = "ALTER TABLE coupons ADD COLUMN code VARCHAR(50) NOT NULL UNIQUE";
    }

    // Add type column if it doesn't exist
    if (!isset($columns['type'])) {
        $alterQueries[] = "ALTER TABLE coupons ADD COLUMN type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage' AFTER code";
    }

    // Add or rename value column
    if (isset($columns['discount'])) {
        $alterQueries[] = "ALTER TABLE coupons CHANGE COLUMN discount value DECIMAL(10,2) NOT NULL";
    } else if (!isset($columns['value'])) {
        $alterQueries[] = "ALTER TABLE coupons ADD COLUMN value DECIMAL(10,2) NOT NULL AFTER type";
    }

    // Add missing columns
    $requiredColumns = [
        'min_rental_days' => "INT NOT NULL DEFAULT 1",
        'usage_limit' => "INT DEFAULT NULL",
        'times_used' => "INT NOT NULL DEFAULT 0",
        'description' => "TEXT",
        'status' => "ENUM('active', 'inactive') DEFAULT 'active'",
        'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];

    foreach ($requiredColumns as $column => $definition) {
        if (!isset($columns[$column])) {
            $alterQueries[] = "ALTER TABLE coupons ADD COLUMN $column $definition";
        }
    }

    // Handle date columns
    if (isset($columns['valid_from'])) {
        $alterQueries[] = "ALTER TABLE coupons CHANGE COLUMN valid_from start_date DATE NOT NULL";
    } else if (!isset($columns['start_date'])) {
        $alterQueries[] = "ALTER TABLE coupons ADD COLUMN start_date DATE NOT NULL";
    }

    if (isset($columns['valid_to'])) {
        $alterQueries[] = "ALTER TABLE coupons CHANGE COLUMN valid_to expiry_date DATE NOT NULL";
    } else if (!isset($columns['expiry_date'])) {
        $alterQueries[] = "ALTER TABLE coupons ADD COLUMN expiry_date DATE NOT NULL";
    }

    // Execute all alter queries
    foreach ($alterQueries as $query) {
        if (!$conn->query($query)) {
            echo "Error executing query: $query\nError: " . $conn->error . "\n";
        }
    }
    echo "Coupons table structure updated successfully\n";
}

// Insert a test coupon if it doesn't exist
$testCouponExists = $conn->query("SELECT id FROM coupons WHERE code = 'WELCOME2025'")->num_rows > 0;

if (!$testCouponExists) {
    $sql = "INSERT INTO coupons (code, type, value, min_rental_days, usage_limit, start_date, expiry_date, description) 
            VALUES ('WELCOME2025', 'percentage', 15.00, 2, 100, '2025-03-21', '2025-12-31', 'Welcome discount for new customers')";

    if ($conn->query($sql)) {
        echo "Test coupon created successfully\n";
    } else {
        echo "Error creating test coupon: " . $conn->error . "\n";
    }
} else {
    echo "Test coupon already exists\n";
}

$conn->close();
?>