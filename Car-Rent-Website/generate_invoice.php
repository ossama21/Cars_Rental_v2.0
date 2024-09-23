<?php
session_start();

require('../fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 24);

// Invoice Header
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 15, 'INVOICE', 0, 1, 'C', true);
$pdf->Ln(10);

// Billing Information
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, 'Bill From:', 0, 0, 'L');
$pdf->Cell(95, 10, 'Bill To:', 0, 1, 'L');

$pdf->SetFont('Arial', '', 12);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(95, 10, 'Cars Rent', 0, 0, 'L', true);
$pdf->Cell(95, 10, htmlspecialchars($_SESSION['username']), 0, 1, 'L', true);
$pdf->Cell(95, 10, 'Email: support@carsrent.com', 0, 0, 'L', true);
$pdf->Cell(95, 10, 'Email: ' . htmlspecialchars($_SESSION['email']), 0, 1, 'L', true);
$pdf->Cell(95, 10, 'Phone: +212 0678963254', 0, 0, 'L', true); // Added company phone number here
$pdf->Cell(95, 10, 'Phone: ' . htmlspecialchars($_SESSION['phone']), 0, 1, 'L', true); // Added user phone number here
$pdf->Ln(5);

// Date Issued
$pdf->SetDrawColor(50, 50, 50);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(100, 10, 'Date Issued:', 0, 0, 'L');
$pdf->Cell(0, 10, htmlspecialchars($_SESSION['startDate']), 0, 1, 'L');
$pdf->Ln(10);

// Booking Information Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(0, 10, 'Booking Information', 0, 1, 'C', true);
$pdf->Ln(5);

// Booking Information Table
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(40, 10, 'Start Date', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'End Date', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Duration (days)', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Amount', 1, 1, 'C', true);  // Adjusted the width for Amount

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, htmlspecialchars($_SESSION['startDate']), 1, 0, 'C');
$pdf->Cell(40, 10, htmlspecialchars($_SESSION['endDate']), 1, 0, 'C');
$pdf->Cell(40, 10, htmlspecialchars($_SESSION['duration']), 1, 0, 'C');

// Calculate the total amount (price * duration)
$totalAmount = $_SESSION['totalAmount'];
$pdf->Cell(70, 10, '$' . number_format($totalAmount, 2), 1, 1, 'C');  // Display the total amount

$pdf->Ln(15);

// Footer
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(0, 10, 'Thank you for choosing Cars Rent!', 0, 1, 'C');

$pdf->SetDrawColor(100, 100, 100);
$pdf->Line(10, 260, 200, 260);

// Output the PDF
$pdf->Output('I', 'Invoice.pdf');
?>
