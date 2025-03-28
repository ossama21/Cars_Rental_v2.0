<?php
/**
 * Car Rental Chatbot API
 * This script provides structured data about cars to the chatbot
 */

// Include database connection
require_once 'connect.php';

// Set content type to JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle different API actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_all_cars':
        echo json_encode(getAvailableCars());
        break;
    case 'search_cars':
        $params = [
            'name' => $_GET['name'] ?? '',
            'brand' => $_GET['brand'] ?? '',
            'type' => $_GET['type'] ?? '',
            'transmission' => $_GET['transmission'] ?? '',
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null
        ];
        echo json_encode(searchCars($params));
        break;
    case 'get_training_data':
        echo json_encode(getTrainingData());
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

// Function to get all available cars
function getAvailableCars() {
    global $conn;
    
    try {
        // Get cars with their availability status
        $stmt = $conn->prepare("SELECT c.*, 
                               (SELECT COUNT(*) FROM bookings b 
                                WHERE b.car_id = c.id 
                                AND CURDATE() BETWEEN b.start_date AND b.end_date) as is_booked
                               FROM cars c
                               ORDER BY c.brand, c.name");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["status" => "success", "data" => $cars];
        } else {
            return ["status" => "error", "message" => "No cars found"];
        }
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
    }
}

// Function to search for cars based on parameters
function searchCars($params) {
    global $conn;
    
    try {
        $query = "SELECT c.* FROM cars c WHERE 1=1";
        $parameters = [];
        
        // Add search parameters to query if they exist
        if (!empty($params['name'])) {
            $query .= " AND (c.name LIKE :name OR c.model LIKE :name)";
            $parameters[':name'] = '%' . $params['name'] . '%';
        }
        
        if (!empty($params['brand'])) {
            $query .= " AND c.brand LIKE :brand";
            $parameters[':brand'] = '%' . $params['brand'] . '%';
        }
        
        if (!empty($params['type'])) {
            $query .= " AND c.type LIKE :type";
            $parameters[':type'] = '%' . $params['type'] . '%';
        }
        
        if (!empty($params['transmission'])) {
            $query .= " AND c.transmission LIKE :transmission";
            $parameters[':transmission'] = '%' . $params['transmission'] . '%';
        }
        
        // Price range
        if (isset($params['min_price']) && is_numeric($params['min_price'])) {
            $query .= " AND c.price >= :min_price";
            $parameters[':min_price'] = $params['min_price'];
        }
        
        if (isset($params['max_price']) && is_numeric($params['max_price'])) {
            $query .= " AND c.price <= :max_price";
            $parameters[':max_price'] = $params['max_price'];
        }
        
        $stmt = $conn->prepare($query);
        $stmt->execute($parameters);
        
        if ($stmt->rowCount() > 0) {
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["status" => "success", "data" => $cars];
        } else {
            return ["status" => "error", "message" => "No cars found matching your criteria"];
        }
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
    }
}

// Function to get car recommendations based on requirements
function getCarRecommendations($requirements) {
    global $conn;
    
    try {
        // Start with basic query
        $query = "SELECT * FROM cars WHERE 1=1";
        $parameters = [];
        
        // Family size to number of seats
        if (isset($requirements['family_size']) && is_numeric($requirements['family_size'])) {
            $familySize = intval($requirements['family_size']);
            $query .= " AND seats >= :seats";
            $parameters[':seats'] = $familySize;
        }
        
        // Trip type to car type (rough mapping)
        if (isset($requirements['trip_type']) && !empty($requirements['trip_type'])) {
            $tripType = strtolower($requirements['trip_type']);
            
            if (strpos($tripType, 'city') !== false) {
                $query .= " AND (type LIKE :type1 OR type LIKE :type2)";
                $parameters[':type1'] = '%compact%';
                $parameters[':type2'] = '%sedan%';
            } 
            else if (strpos($tripType, 'highway') !== false || strpos($tripType, 'long') !== false) {
                $query .= " AND (type LIKE :type1 OR type LIKE :type2)";
                $parameters[':type1'] = '%sedan%';
                $parameters[':type2'] = '%luxury%';
            }
            else if (strpos($tripType, 'off-road') !== false || strpos($tripType, 'offroad') !== false) {
                $query .= " AND (type LIKE :type1 OR type LIKE :type2)";
                $parameters[':type1'] = '%suv%';
                $parameters[':type2'] = '%crossover%';
            }
        }
        
        // Budget constraints
        if (isset($requirements['budget']) && !empty($requirements['budget'])) {
            $budget = strtolower($requirements['budget']);
            
            if (strpos($budget, 'low') !== false || strpos($budget, 'cheap') !== false || strpos($budget, 'economy') !== false) {
                $query .= " AND price <= :max_price";
                $parameters[':max_price'] = 75; // Adjust based on your pricing
            } 
            else if (strpos($budget, 'mid') !== false || strpos($budget, 'moderate') !== false || strpos($budget, 'average') !== false) {
                $query .= " AND price BETWEEN :min_price AND :max_price";
                $parameters[':min_price'] = 75;
                $parameters[':max_price'] = 150;
            }
            else if (strpos($budget, 'high') !== false || strpos($budget, 'luxury') !== false || strpos($budget, 'premium') !== false) {
                $query .= " AND price >= :min_price";
                $parameters[':min_price'] = 150;
            }
        }
        
        $stmt = $conn->prepare($query);
        $stmt->execute($parameters);
        
        if ($stmt->rowCount() > 0) {
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["status" => "success", "data" => $cars];
        } else {
            return ["status" => "error", "message" => "No cars found matching your requirements"];
        }
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
    }
}

// Function to get car details by ID
function getCarDetails($carId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id");
        $stmt->bindParam(':id', $carId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $car = $stmt->fetch(PDO::FETCH_ASSOC);
            return ["status" => "success", "data" => $car];
        } else {
            return ["status" => "error", "message" => "Car not found"];
        }
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
    }
}

// Function to get car availability for specific dates
function getCarAvailability($carId, $startDate, $endDate) {
    global $conn;
    
    try {
        // Check if there are any bookings for the car within the given date range
        $stmt = $conn->prepare("SELECT COUNT(*) as booking_count 
                               FROM bookings 
                               WHERE car_id = :car_id 
                               AND ((:start_date BETWEEN start_date AND end_date) 
                                   OR (:end_date BETWEEN start_date AND end_date) 
                                   OR (start_date BETWEEN :start_date AND :end_date))");
        
        $stmt->bindParam(':car_id', $carId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['booking_count'] > 0) {
            return ["status" => "success", "available" => false, "message" => "Car is not available for the selected dates"];
        } else {
            return ["status" => "success", "available" => true, "message" => "Car is available for the selected dates"];
        }
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
    }
}

// Function to get chatbot training data
function getTrainingData() {
    // Include the training data file
    require_once 'chatbot_training_data.php';
    
    // Check if the data was loaded successfully
    if (isset($chatbot_training_data) && is_array($chatbot_training_data)) {
        return ["status" => "success", "data" => $chatbot_training_data];
    } else {
        return ["status" => "error", "message" => "Could not load training data"];
    }
}
?>