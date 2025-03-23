<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Check admin access
checkAdminAccess();

header('Content-Type: application/json');

if (!isset($_POST['image_id']) || !is_numeric($_POST['image_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
    exit;
}

if (!isset($_FILES['new_image'])) {
    echo json_encode(['success' => false, 'message' => 'No image file received']);
    exit;
}

$imageId = $_POST['image_id'];
$file = $_FILES['new_image'];

try {
    $conn->begin_transaction();

    // Get current image details
    $stmt = $conn->prepare("SELECT car_id, image_path FROM car_images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentImage = $result->fetch_assoc();

    if (!$currentImage) {
        throw new Exception('Image not found');
    }

    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, JPEG, and PNG are allowed');
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File is too large. Maximum size is 5MB');
    }

    // Create directory if it doesn't exist
    $carImageDir = "../images/cars/" . $currentImage['car_id'];
    if (!file_exists($carImageDir)) {
        mkdir($carImageDir, 0777, true);
    }

    // Generate new filename
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $fileExtension;
    $newRelativePath = "images/cars/" . $currentImage['car_id'] . '/' . $newFileName;
    $newFullPath = "../" . $newRelativePath;

    // Delete old file if it exists
    $oldFullPath = "../" . $currentImage['image_path'];
    if (file_exists($oldFullPath)) {
        unlink($oldFullPath);
    }

    // Move new file
    if (!move_uploaded_file($file['tmp_name'], $newFullPath)) {
        throw new Exception('Failed to save new image');
    }

    // Update database
    $stmt = $conn->prepare("UPDATE car_images SET image_path = ? WHERE id = ?");
    $stmt->bind_param("si", $newRelativePath, $imageId);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    // If we created a new file but the transaction failed, clean it up
    if (isset($newFullPath) && file_exists($newFullPath)) {
        unlink($newFullPath);
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>