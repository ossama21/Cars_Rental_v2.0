<?php
header('Content-Type: application/json');
session_start();

include 'connect.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';
$rental_days = intval($data['rental_days'] ?? 0);

if (empty($code)) {
    echo json_encode(['success' => false, 'error' => 'Coupon code is required']);
    exit;
}

try {
    // Check if coupon exists and is valid
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expiry_date > CURRENT_TIMESTAMP");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid or expired coupon code']);
        exit;
    }

    $coupon = $result->fetch_assoc();

    // Validate minimum rental days if specified
    if ($coupon['min_rental_days'] > 0 && $rental_days < $coupon['min_rental_days']) {
        echo json_encode([
            'success' => false, 
            'error' => "This coupon requires a minimum rental period of {$coupon['min_rental_days']} days"
        ]);
        exit;
    }

    // Return coupon details for frontend processing
    echo json_encode([
        'success' => true,
        'message' => 'Coupon applied successfully',
        'coupon' => [
            'code' => $coupon['code'],
            'discount' => floatval($coupon['discount_percentage']),
            'min_rental_days' => $coupon['min_rental_days']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error validating coupon']);
}