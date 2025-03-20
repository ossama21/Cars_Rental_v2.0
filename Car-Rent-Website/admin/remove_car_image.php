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

    // Get image details
    $stmt = $conn->prepare("SELECT * FROM car_images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();

    if (!$image) {
        throw new Exception('Image not found');
    }

    // Count total images for this car
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM car_images WHERE car_id = ?");
    $stmt->bind_param("i", $image['car_id']);
    $stmt->execute();
    $totalImages = $stmt->get_result()->fetch_assoc()['total'];

    // Don't allow removal if it's the only image
    if ($totalImages <= 1) {
        throw new Exception('Cannot remove the only image. Cars must have at least one image.');
    }

    // If removing primary image, set another image as primary
    if ($image['is_primary']) {
        $stmt = $conn->prepare("UPDATE car_images SET is_primary = 1 WHERE car_id = ? AND id != ? LIMIT 1");
        $stmt->bind_param("ii", $image['car_id'], $imageId);
        $stmt->execute();
    }

    // Delete image record
    $stmt = $conn->prepare("DELETE FROM car_images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();

    // Delete physical file
    $filePath = "../" . $image['image_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>