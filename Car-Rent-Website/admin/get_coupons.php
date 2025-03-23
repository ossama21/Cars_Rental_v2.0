<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Check admin access
checkAdminAccess();

header('Content-Type: application/json');

try {
    $sql = "SELECT c.*, 
            (SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = c.id) as times_used 
            FROM coupons c 
            ORDER BY c.created_at DESC";
    
    $result = $conn->query($sql);
    $coupons = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format the discount display
            if ($row['discount_type'] === 'percentage') {
                $discountDisplay = $row['discount_value'] . '%';
            } else {
                $discountDisplay = '$' . number_format($row['discount_value'], 2);
            }
            
            $coupons[] = [
                'id' => $row['id'],
                'code' => $row['code'],
                'discount' => $discountDisplay,
                'validUntil' => date('M d, Y', strtotime($row['end_date'])),
                'usage' => $row['times_used'] . '/' . $row['usage_limit'],
                'status' => $row['status']
            ];
        }
    }
    
    echo json_encode(['success' => true, 'coupons' => $coupons]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>