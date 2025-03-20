<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Check admin access
checkAdminAccess();

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
    exit;
}

$imageId = $_GET['id'];

try {
    $conn->begin_transaction();

    // Get image details first
    $stmt = $conn->prepare("SELECT car_id FROM car_images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();

    if (!$image) {
        throw new Exception('Image not found');
    }

    // Remove primary flag from all images of this car
    $stmt = $conn->prepare("UPDATE car_images SET is_primary = 0 WHERE car_id = ?");
    $stmt->bind_param("i", $image['car_id']);
    $stmt->execute();

    // Set new primary image
    $stmt = $conn->prepare("UPDATE car_images SET is_primary = 1 WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>