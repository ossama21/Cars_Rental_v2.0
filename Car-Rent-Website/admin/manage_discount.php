<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Check admin access
checkAdminAccess();

// Handle discount removal
if (isset($_GET['remove']) && isset($_GET['car_id'])) {
    $car_id = (int)$_GET['car_id'];
    $sql = "DELETE FROM car_discounts WHERE car_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $car_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Discount removed successfully.";
    } else {
        $_SESSION['error'] = "Failed to remove discount.";
    }
    header("Location: manage_cars.php");
    exit();
}

// Handle POST request for applying discounts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Validate dates
    if ($start_date > $end_date) {
        $_SESSION['error'] = "End date must be after start date.";
        header("Location: manage_cars.php");
        exit();
    }

    // Validate discount value
    if ($discount_type === 'percentage' && ($discount_value <= 0 || $discount_value > 100)) {
        $_SESSION['error'] = "Percentage discount must be between 0 and 100.";
        header("Location: manage_cars.php");
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Handle bulk discount
        if (isset($_POST['bulk_discount'])) {
            $discount_scope = $_POST['discount_scope'];
            $where_clause = "";
            
            switch ($discount_scope) {
                case 'all':
                    // No additional where clause needed
                    break;
                    
                case 'available':
                    // Get cars that are not currently rented or reserved
                    $where_clause = " WHERE id NOT IN (
                        SELECT DISTINCT car_id FROM services 
                        WHERE (CURRENT_DATE BETWEEN DATE(start_date) AND DATE(end_date))
                        OR (DATE(start_date) > CURRENT_DATE)
                    )";
                    break;
                    
                case 'brand':
                    $brand = $_POST['brand'];
                    $where_clause = " WHERE brand = ?";
                    break;
            }
            
            // Remove existing discounts for affected cars
            $delete_sql = "DELETE FROM car_discounts WHERE car_id IN (SELECT id FROM cars" . $where_clause . ")";
            if ($discount_scope === 'brand') {
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("s", $brand);
                $delete_stmt->execute();
            } else {
                $conn->query($delete_sql);
            }
            
            // Insert new discounts
            $insert_sql = "INSERT INTO car_discounts (car_id, discount_type, discount_value, start_date, end_date) 
                          SELECT id, ?, ?, ?, ? FROM cars" . $where_clause;
            
            if ($discount_scope === 'brand') {
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("sdsss", $discount_type, $discount_value, $start_date, $end_date, $brand);
            } else {
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("sdss", $discount_type, $discount_value, $start_date, $end_date);
            }
            
            if ($stmt->execute()) {
                $affected = $stmt->affected_rows;
                $_SESSION['success'] = "Bulk discount applied successfully to {$affected} cars.";
            } else {
                throw new Exception("Failed to apply bulk discount.");
            }
        }
        // Handle selective discount (multiple selected cars)
        elseif (isset($_POST['selective_discount']) && isset($_POST['selected_cars']) && is_array($_POST['selected_cars'])) {
            $selected_cars = array_map('intval', $_POST['selected_cars']);
            
            if (empty($selected_cars)) {
                throw new Exception("No cars selected for discount application.");
            }
            
            // Remove existing discounts for selected cars
            $placeholders = str_repeat('?,', count($selected_cars) - 1) . '?';
            $delete_sql = "DELETE FROM car_discounts WHERE car_id IN ($placeholders)";
            $delete_stmt = $conn->prepare($delete_sql);
            
            // Create dynamic binding parameters
            $delete_types = str_repeat('i', count($selected_cars));
            $delete_params = $selected_cars;
            
            // Use reflection to bind parameters dynamically
            $delete_ref = new ReflectionClass('mysqli_stmt');
            $delete_method = $delete_ref->getMethod('bind_param');
            $delete_method->invokeArgs($delete_stmt, refValues(array_merge(array($delete_types), $delete_params)));
            
            $delete_stmt->execute();
            
            // Insert new discounts for each selected car
            $insert_sql = "INSERT INTO car_discounts (car_id, discount_type, discount_value, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            
            $success_count = 0;
            foreach ($selected_cars as $car_id) {
                $insert_stmt->bind_param("isdss", $car_id, $discount_type, $discount_value, $start_date, $end_date);
                if ($insert_stmt->execute()) {
                    $success_count++;
                }
            }
            
            if ($success_count > 0) {
                $_SESSION['success'] = "Discounts applied successfully to {$success_count} cars.";
            } else {
                throw new Exception("Failed to apply discounts to selected cars.");
            }
        }
        // Handle individual car discount
        elseif (isset($_POST['car_id'])) {
            $car_id = (int)$_POST['car_id'];
            
            // Remove existing discount for this car
            $delete_sql = "DELETE FROM car_discounts WHERE car_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $car_id);
            $delete_stmt->execute();
            
            // Insert new discount
            $insert_sql = "INSERT INTO car_discounts (car_id, discount_type, discount_value, start_date, end_date) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("isdss", $car_id, $discount_type, $discount_value, $start_date, $end_date);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Discount applied successfully.";
            } else {
                throw new Exception("Failed to apply discount.");
            }
        } else {
            throw new Exception("Invalid discount request.");
        }
        
        // Commit transaction
        $conn->commit();
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: manage_cars.php");
    exit();
}

/**
 * Helper function to pass parameters by reference
 */
function refValues($arr) {
    if (strnatcmp(phpversion(), '5.3') >= 0) {
        $refs = array();
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    return $arr;
}
?>