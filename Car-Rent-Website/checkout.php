<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "car_rent";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['car_id'])) {
        $car_id = intval($_POST['car_id']);

        // Query the database for the specific car by ID
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $car = $result->fetch_assoc();
            echo json_encode(['car' => $car]);
        } else {
            echo json_encode(['car' => null]);
        }

        $stmt->close();
      
        exit;
    }

    // Handle Bank Transfer form submission
    if (isset($_POST['bank-account']) && isset($_POST['bank-routing'])) {
        $payment_method = 'Bank Transfer';
        $account_number = $_POST['bank-account'];
        $routing_number = $_POST['bank-routing'];
        $payment_details = "Account Number: $account_number, Routing Number: $routing_number";

        // Insert payment details into the 'services' table
        $stmt = $conn->prepare("INSERT INTO services (payment_method, payment_details) VALUES (?, ?)");
        $stmt->bind_param("ss", $payment_method, $payment_details);

        if ($stmt->execute()) {
            echo "Payment details saved successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARSRENT - Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="./images/image.png">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #2ecc71;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: var(--light-color);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .car-details, .form-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .car-image img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .car-info h1 {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        
        .price {
            font-size: 1.5rem;
            color: var(--accent-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .rating {
            color: #f39c12;
            margin-bottom: 1rem;
        }
        
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .features span {
            display: flex;
            align-items: center;
        }
        
        .features i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .form-container h2 {
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        form {
            display: grid;
            gap: 1rem;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="datetime-local"],
        input[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }
        
        input[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
        
        .payment-methods {
            margin-top: 2rem;
        }
        
        .blue-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .blue-btn:hover {
            background-color: #2980b9;
        }
        
        .payment-form {
            background-color: var(--light-color);
            border-radius: 5px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .payment-form h4 {
            margin-bottom: 1rem;
        }
        
        .payment-form label {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .payment-form input {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .cancel-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .cancel-btn:hover {
            background-color: #c0392b;
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkout-grid">
            <!-- Car Details Section -->
            <div class="car-details">
                <div class="car-image"> 
                    <img id="car-image" alt="Car Image" src="/api/placeholder/400/300"/>
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
                        <span><i class="fas fa-tag"></i> <span id="car-brand">Brand</span></span>
                    </div>
                </div>
            </div>

            <!-- Booking and Payment Forms Section -->
            <div class="form-container">
                <!-- Booking Information Form -->
                <div class="booking-info">
                    <h2>Booking Information</h2>
                    <form id="bookingForm" action="confirmation.php" method="post">
                    <input type="hidden" name="car_id" id="car_id" value="" /> 
                        <input placeholder="Full Name" type="text" name="name" required />
                        <input placeholder="Phone" type="text" name="phone" required />
                        <input type="email" name="email" required placeholder="Enter your email">
                        <input type="datetime-local" id="startDate" name="startDate" required />
                        <input type="datetime-local" id="endDate" name="endDate" required />
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
                        <form method="POST" action="checkout.php">
                            <label for="bank-account">Account Number:</label>
                            <input type="text" id="bank-account" name="bank-account" required>
                            <label for="bank-routing">Routing Number:</label>
                            <input type="text" id="bank-routing" name="bank-routing" required>
                            <button type="submit" class="blue-btn submit-btn">Submit</button>
                        </form>
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
            </div>
        </div>
    </div>

    <script type="module" src="./js/checkout.js"></script>

    <script>
        function getCurrentDateTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = ('0' + (now.getMonth() + 1)).slice(-2);
            const day = ('0' + now.getDate()).slice(-2);
            const hours = ('0' + now.getHours()).slice(-2);
            const minutes = ('0' + now.getMinutes()).slice(-2);
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const now = getCurrentDateTime();
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');

            startDate.setAttribute('min', now);
            endDate.setAttribute('min', now);

            startDate.addEventListener('change', function () {
                const selectedStartDate = startDate.value;
                if (selectedStartDate) {
                    endDate.setAttribute('min', selectedStartDate);
                }
            });
        });
    </script>
</body>
</html>