<?php
// Get booking details
$name = $_POST['name'];
$phone = $_POST['phone'];
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$quantity = $_POST['quantity'];

// Calculate duration of rental
$startDateObj = new DateTime($startDate);
$endDateObj = new DateTime($endDate);
$duration = $startDateObj->diff($endDateObj)->days;

// Get car details
$carName = "Hyundai i20"; // You would replace this with dynamic data

// Display confirmation
echo "<h1>Congratulations, $name!</h1>";
echo "<p>You have successfully reserved the car: $carName</p>";
echo "<p>Phone: $phone</p>";
echo "<p>Start Date: $startDate</p>";
echo "<p>End Date: $endDate</p>";
echo "<p>Duration: $duration days</p>";
echo "<p>Quantity: $quantity</p>";

// Add a button to generate a PDF
echo "<form action='generate_pdf.php' method='post'>";
echo "<input type='hidden' name='name' value='$name'>";
echo "<input type='hidden' name='phone' value='$phone'>";
echo "<input type='hidden' name='startDate' value='$startDate'>";
echo "<input type='hidden' name='endDate' value='$endDate'>";
echo "<input type='hidden' name='duration' value='$duration'>";
echo "<input type='hidden' name='carName' value='$carName'>";
echo "<button type='submit'>Generate PDF</button>";
echo "</form>";
?>
