<?php
session_start();
require('../fpdf.php');

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "car_rent";

// Create database connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define custom PDF class
class PDF extends FPDF {
    // Page header
    function Header() {
        // Logo
        $this->Image('images/image.png', 10, 10, 30);
        // Company info
        $this->SetFont('Arial', 'B', 15);
        $this->SetXY(45, 10);
        $this->Cell(100, 10, 'CARSRENT', 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->SetXY(45, 20);
        $this->Cell(100, 5, 'The premium car rental service', 0, 1, 'L');
        $this->SetXY(45, 25);
        $this->Cell(100, 5, 'Phone: +1 234 567 890', 0, 1, 'L');
        $this->SetXY(45, 30);
        $this->Cell(100, 5, 'Email: info@carsrent.com', 0, 1, 'L');
        
        // Invoice Title
        $this->SetFont('Arial', 'B', 20);
        $this->SetXY(10, 45);
        $this->Cell(190, 10, 'INVOICE', 0, 1, 'C');
        
        // Line break
        $this->Ln(5);
    }

    // Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->SetY(-25);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 10, 'Thank you for choosing CARSRENT. We hope to see you again soon.', 0, 0, 'C');
    }
}

// Initialize variables
$booking = null;
$bookingId = 0;
$car = null;

// Get booking ID from URL parameter
if (isset($_GET['booking_id']) && is_numeric($_GET['booking_id'])) {
    $bookingId = (int)$_GET['booking_id'];
    
    // Prepare statement to get booking details
    $stmt = $conn->prepare("SELECT s.*, c.* FROM services s 
                           LEFT JOIN cars c ON s.car_id = c.id
                           WHERE s.id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $booking = $row;
        $car = [
            'id' => $row['car_id'],
            'name' => $row['name'],
            'brand' => $row['brand'],
            'model' => $row['model'],
            'transmission' => $row['transmission'],
            'interior' => $row['interior'],
            'price' => $row['price'],
            'image' => $row['image']
        ];
    }
    $stmt->close();
}
// If no booking ID in URL, check session
else if (isset($_SESSION['booking'])) {
    $booking = $_SESSION['booking'];
    $car = $booking['car'];
}

// If no booking data available, show error
if (!$booking) {
    die("No booking information found. Please go back and try again.");
}

// Calculate total amount
$duration = isset($booking['duration']) ? $booking['duration'] : (floor((strtotime($booking['endDate']) - strtotime($booking['startDate'])) / (60 * 60 * 24)) + 1);
$basePrice = $car['price'] * $duration;
$insuranceFee = 25;
$preorderFee = $booking['status'] === 'preorder' ? 15 : 0;  // Updated to $15
$totalAmount = $basePrice + $insuranceFee + $preorderFee;

// Create PDF
$pdf = new PDF();
$pdf->AliasNbPages(); // For page numbers
$pdf->AddPage();

// Customer Information
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(240, 240, 240); 
$pdf->Cell(190, 10, 'CUSTOMER DETAILS', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 10);
$customerName = isset($booking['username']) ? $booking['username'] : '';
$pdf->Cell(40, 8, 'Customer Name:', 1);
$pdf->Cell(150, 8, $customerName, 1, 1);

$email = isset($booking['email']) ? $booking['email'] : '';
$pdf->Cell(40, 8, 'Email:', 1);
$pdf->Cell(150, 8, $email, 1, 1);

$phone = isset($booking['phone']) ? $booking['phone'] : '';
$pdf->Cell(40, 8, 'Phone:', 1);
$pdf->Cell(150, 8, $phone, 1, 1);

// Add booking reference
$pdf->Cell(40, 8, 'Booking Ref:', 1);
$pdf->Cell(150, 8, '#' . ($bookingId ? $bookingId : 'TEMP' . rand(1000, 9999)), 1, 1);

// Booking Details
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'BOOKING DETAILS', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 8, 'Car:', 1);
$pdf->Cell(150, 8, $car['brand'] . ' ' . $car['name'] . ' - ' . $car['model'], 1, 1);

// Add car image if available
if (isset($car['image']) && file_exists($car['image'])) {
    $pdf->Cell(190, 5, '', 0, 1); // Space
    $pdf->Cell(190, 8, 'Car Image:', 0, 1);
    $pdf->Image($car['image'], 10, $pdf->GetY(), 60);
    $pdf->Cell(190, 45, '', 0, 1); // Space for image
}

// More details
$pdf->Cell(40, 8, 'Transmission:', 1);
$pdf->Cell(150, 8, $car['transmission'], 1, 1);

$pdf->Cell(40, 8, 'Interior:', 1);
$pdf->Cell(150, 8, $car['interior'], 1, 1);

$startDate = isset($booking['startDate']) ? $booking['startDate'] : 
             (isset($booking['start_date']) ? $booking['start_date'] : 'N/A');
$pdf->Cell(40, 8, 'Start Date:', 1);
$pdf->Cell(150, 8, $startDate, 1, 1);

$endDate = isset($booking['endDate']) ? $booking['endDate'] : 
           (isset($booking['end_date']) ? $booking['end_date'] : 'N/A');
$pdf->Cell(40, 8, 'End Date:', 1);
$pdf->Cell(150, 8, $endDate, 1, 1);

$pdf->Cell(40, 8, 'Duration:', 1);
$pdf->Cell(150, 8, $duration . ' days', 1, 1);

// Payment Summary
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'PAYMENT SUMMARY', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 10);

// Daily rate
$pdf->Cell(120, 8, 'Daily Rate:', 1);
$pdf->Cell(70, 8, '$' . number_format($price, 2), 1, 1, 'R');

// Rental duration
$pdf->Cell(120, 8, 'Rental Duration:', 1);
$pdf->Cell(70, 8, $duration . ' days', 1, 1, 'R');

// Base amount
$baseAmount = $duration * $price;
$pdf->Cell(120, 8, 'Base Amount:', 1);
$pdf->Cell(70, 8, '$' . number_format($baseAmount, 2), 1, 1, 'R');

// Insurance fee
$pdf->Cell(120, 8, 'Insurance Fee:', 1);
$pdf->Cell(70, 8, '$25.00', 1, 1, 'R');

// Add preorder note if applicable
if ($booking['status'] === 'preorder') {
    $pdf->SetFillColor(240, 248, 255);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'This is a pre-order booking. The car will be reserved for your selected dates.', 0, 1, 'L', true);
    $pdf->Ln(5);
}

// Add line items
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Rental Details', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(130, 7, "Daily Rate ({$duration} days @ $" . number_format($car['price'], 2) . "/day)", 0, 0);
$pdf->Cell(30, 7, '$' . number_format($basePrice, 2), 0, 1, 'R');
$pdf->Cell(130, 7, "Insurance Fee", 0, 0);
$pdf->Cell(30, 7, '$' . number_format($insuranceFee, 2), 0, 1, 'R');

if ($preorderFee > 0) {
    $pdf->Cell(130, 7, "Pre-order Fee", 0, 0);
    $pdf->Cell(30, 7, '$' . number_format($preorderFee, 2), 0, 1, 'R');
}

// Total amount
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(120, 10, 'TOTAL AMOUNT:', 1);
$pdf->Cell(70, 10, '$' . number_format($totalAmount, 2), 1, 1, 'R');

// Payment method
$paymentMethod = isset($booking['paymentMethod']) ? $booking['paymentMethod'] : 
                (isset($booking['payment_method']) ? $booking['payment_method'] : 'N/A');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(120, 8, 'Payment Method:', 1);
$pdf->Cell(70, 8, ucfirst($paymentMethod), 1, 1, 'R');

// Terms and conditions
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'TERMS AND CONDITIONS', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 9);
$terms = [
    "1. Please bring your driver's license and credit card for verification at pickup.",
    "2. The car must be returned with the same fuel level as at pickup.",
    "3. Any damages beyond normal wear and tear will be charged to the customer.",
    "4. Late returns may be subject to additional charges.",
    "5. Smoking is not allowed in the vehicle. A cleaning fee will be applied if violated.",
    "6. The vehicle should not be taken off-road or outside the permitted area.",
    "7. In case of breakdown or accident, please contact our emergency hotline immediately."
];

foreach ($terms as $term) {
    $pdf->MultiCell(190, 5, $term, 1);
}

// Add invoice date and time
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(190, 5, 'Invoice generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'R');

// Output PDF
$pdf->Output('CARSRENT_Invoice_' . $bookingId . '.pdf', 'I');

// Close database connection
$conn->close();
?>
