<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Check admin access
checkAdminAccess();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Coupon ID is required');
    }

    $id = intval($_GET['id']);
    
    // Delete the coupon
    $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Coupon deleted successfully']);
        } else {
            throw new Exception('Coupon not found');
        }
    } else {
        throw new Exception('Failed to delete coupon');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>