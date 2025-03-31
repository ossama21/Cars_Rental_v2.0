<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Ensure only admin has access
checkAdminAccess();

header('Content-Type: application/json');

// Check if required parameters are provided
if (!isset($_POST['car_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing car ID']);
    exit;
}

$car_id = $_POST['car_id'];
$service_id = isset($_POST['service_id']) ? $_POST['service_id'] : null;

try {
    // Start transaction
    $conn->begin_transaction();
    
    // If service_id is provided, use it for a more precise deletion
    if ($service_id) {
        $stmt = $conn->prepare("DELETE FROM services WHERE id = ? AND car_id = ?");
        $stmt->bind_param("ii", $service_id, $car_id);
    } else {
        // Otherwise delete based on future bookings for this car
        $currentDate = date('Y-m-d');
        $stmt = $conn->prepare("DELETE FROM services WHERE car_id = ? AND DATE(start_date) > ?");
        $stmt->bind_param("is", $car_id, $currentDate);
    }
    
    $result = $stmt->execute();
    
    if ($result) {
        // If rows were affected (booking was deleted)
        if ($stmt->affected_rows > 0) {
            $conn->commit();
            
            // Create success message
            echo json_encode([
                'success' => true, 
                'message' => 'Reservation cancelled successfully',
                'affected_rows' => $stmt->affected_rows
            ]);
            exit;
        } else {
            // No reservation found
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'No matching reservation found']);
            exit;
        }
    } else {
        // SQL error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error executing query: ' . $stmt->error]);
        exit;
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}