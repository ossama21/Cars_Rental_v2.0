<?php
session_start();

require('../fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 24);

$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 15, 'INVOICE', 0, 1, 'C', true);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, 'Bill From:', 0, 0, 'L');
$pdf->Cell(95, 10, 'Bill To:', 0, 1, 'L');

$pdf->SetFont('Arial', '', 12);

$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(95, 10, 'Cars Rent', 0, 0, 'L', true);
$pdf->Cell(95, 10, htmlspecialchars($_SESSION['username']), 0, 1, 'L', true);
$pdf->Cell(95, 10, 'Email: support@carsrent.com', 0, 0, 'L', true);
$pdf->Cell(95, 10, 'Email: ' . htmlspecialchars($_SESSION['email']), 0, 1, 'L', true);
$pdf->Ln(5);

$pdf->SetDrawColor(50, 50, 50);

$pdf->Ln(10);

$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(100, 10, 'Date Issued:', 0, 0, 'L');
$pdf->Cell(0, 10, htmlspecialchars($_SESSION['startDate']), 0, 1, 'L');
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(0, 10, 'Booking Information', 0, 1, 'C', true);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(40, 10, 'End Date', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Duration (days)', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Phone', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, htmlspecialchars($_SESSION['endDate']), 1, 0, 'C');
$pdf->Cell(40, 10, htmlspecialchars($_SESSION['duration']), 1, 0, 'C');
$pdf->Cell(40, 10, htmlspecialchars($_SESSION['quantity']), 1, 0, 'C');
$pdf->Cell(60, 10, htmlspecialchars($_SESSION['phone']), 1, 1, 'C');

$pdf->Ln(15);

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(0, 10, 'Thank you for choosing Cars Rent!', 0, 1, 'C');

$pdf->SetDrawColor(100, 100, 100);
$pdf->Line(10, 260, 200, 260);

$pdf->Output('I', 'Invoice.pdf');
?>
