<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Check admin access
checkAdminAccess();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    // Validate required fields
    $required_fields = ['code', 'discount_type', 'discount_value', 'start_date', 'end_date'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['discount_type'];
    $value = floatval($_POST['discount_value']);
    $min_rental_days = isset($_POST['min_rental_days']) ? intval($_POST['min_rental_days']) : 1;
    $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'] ?? '';
    $status = isset($_POST['is_active']) && $_POST['is_active'] === 'on' ? 'active' : 'inactive';

    // Validate discount value
    if ($type === 'percentage' && ($value <= 0 || $value > 100)) {
        throw new Exception("Percentage discount must be between 0 and 100");
    } elseif ($type === 'fixed' && $value <= 0) {
        throw new Exception("Fixed discount amount must be greater than 0");
    }

    // Validate dates
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    if ($start > $end) {
        throw new Exception("End date must be after start date");
    }

    // Check if coupon code already exists
    $stmt = $conn->prepare("SELECT id FROM coupons WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Coupon code already exists");
    }

    // Insert new coupon
    $stmt = $conn->prepare("INSERT INTO coupons (code, type, value, min_rental_days, usage_limit, start_date, expiry_date, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsissss", $code, $type, $value, $min_rental_days, $usage_limit, $start_date, $end_date, $description, $status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Coupon created successfully']);
    } else {
        throw new Exception("Failed to create coupon: " . $stmt->error);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>