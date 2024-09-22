<?php
session_start();
$host="localhost";
$user="root";
$pass="";
$db="car_rent";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if it's an AJAX request and if car_id is passed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $car_id = intval($_POST['car_id']);

    // Query the database for the specific car by ID
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $car = $result->fetch_assoc();
        // Return car details as JSON
        echo json_encode(['car' => $car]);
    } else {
        echo json_encode(['car' => null]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Fallback to default behavior for non-AJAX requests (rendering HTML, etc.)
// Your existing HTML code goes here
?>
<html>
<head>
    <title>CARSRENT - Checkout</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/> 
    <link rel="stylesheet" type="text/css" href="./css/checkout.css">
    <link rel="icon" type="image/png" href="./images/image.png"> 
</head>
<body>
    <div class="container">
        <!-- Car Details Section -->
        <div class="car-details">
            <div class="car-image"> 
                <img id="car-image" alt="Car Image" height="300" width="400"/>
            </div>
            <div class="car-info">
                <h1 id="car-name">Car Name</h1>
                <div id="car-price" class="price">$0.00 / Day</div> 
                <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                    <span>4.5 ratings</span>
                </div>
                <p id="car-description">Car description goes here.</p>
                <div class="features">
                    <span><i class="fas fa-car"></i> <span id="car-model">Model</span></span>
                    <span><i class="fas fa-cogs"></i> <span id="car-transmission">Transmission</span></span>
                    <span><i class="fas fa-couch"></i> <span id="car-interior">Interior</span></span>
                    <span><i class="fas fa-car"></i> <span id="car-brand">Brand</span></span>
                </div>
            </div>
        </div>

<!-- Booking and Payment Forms Section -->
<div class="form-container">
    <!-- Booking Information Form -->
    <div class="booking-info">
    <h2>Booking Information</h2>
    <form id="bookingForm" action="confirmation.php" method="post">
        <div>
            <input placeholder="Full Name" type="text" name="name" required />
            <input placeholder="Phone" type="text" name="phone" required />
            
            <input type="email" name="email" required placeholder="Enter your email">
     
        </div> 
        <div>
            <input type="date" name="startDate" required />
            <input type="date" name="endDate" required />
        </div>
      
        <input type="submit" value="Reserve Now" />
    </form>
</div>


    <!-- Payment Information Form -->
    <div class="payment-methods">
        <h2>Payment Information</h2> 
        <button id="bank-button" class="blue-btn">Direct Bank Transfer</button>
        <button id="cheque-button" class="blue-btn">Cheque Payment</button>
        <button id="mastercard-button" class="blue-btn">Master Card</button>
        <button id="paypal-button" class="blue-btn">PayPal</button>
    
        <!-- Bank Transfer Form -->
    <div class="payment-form" id="bank-form" style="display:none;">
        <h4>Bank Transfer Details</h4>
        <label for="bank-account">Account Number:</label>
        <input type="text" id="bank-account" name="bank-account" required>
        <label for="bank-routing">Routing Number:</label>
        <input type="text" id="bank-routing" name="bank-routing" required>
        <button  type="submit" class="clicked blue-btn submit-btn">Submit</button>
        <button class="cancel-btn">Cancel</button>
    </div>

    <!-- Cheque Form -->
    <div class="payment-form" id="cheque-form" style="display:none;">
        <h4>Cheque Payment Details</h4> 
        <label for="cheque-number">Cheque Number:</label>
        <input type="text" id="cheque-number" name="cheque-number" required> 
        <label for="cheque-date">Date:</label>
        <input type="date" id="cheque-date" name="cheque-date" required>
        <button class="blue-btn submit-btn">Submit</button>
        <button class="cancel-btn">Cancel</button>
    </div>

    <!-- MasterCard Form -->
    <div class="payment-form" id="mastercard-form" style="display:none;">
        <h4>Enter MasterCard Details</h4>
        <label for="mastercard-number">Card Number:</label>
        <input type="text" id="mastercard-number" placeholder="1234 5678 9012 3456" name="mastercard-number" required>
        <label for="mastercard-expiry">Expiry Date:</label>
        <input type="month" id="mastercard-expiry" name="mastercard-expiry" required>
        <label for="mastercard-cvc">CVV:</label>
        <input type="text" id="mastercard-cvc" name="mastercard-cvc" required>
        <button class="blue-btn submit-btn">Submit</button>
        <button class="cancel-btn">Cancel</button>
    </div> 

    <!-- PayPal Form -->
    <div class="payment-form" id="paypal-form" style="display:none;">
        <h4>Connect to your PayPal Account</h4>
        <label for="paypal-email">PayPal Email:</label>
        <input type="email" id="paypal-email" name="paypal-email" required>
        <button class="blue-btn submit-btn">Submit</button>
        <button class="cancel-btn">Cancel</button>
    </div>
</div>


    <script type="module" src="./js/checkout.js"></script> <!-- Link to the JS file -->
    
</body>
</html>
