<?php
session_start();
include 'config.php';
require 'src/fpdf186/fpdf.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$note_id = $conn->real_escape_string($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch note and user data
$sql = "SELECT n.title, n.description, n.created_at, u.first_name 
        FROM notes n 
        JOIN registered u ON n.user_id = u.id 
        WHERE n.id = '$note_id' AND n.user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    echo "Note not found or you do not have permission to download this note.";
    exit();
}

$note = $result->fetch_assoc();
$created_at = date('F j, Y', strtotime($note['created_at']));
$username = htmlspecialchars($note['first_name']);
$title = strtoupper($note['title']);
$description = $note['description'];

// Extend FPDF to draw border on every page
class PDF extends FPDF {
    function Header() {
        // Draw border
        $this->SetLineWidth(0.3);
        $this->Rect(5, 5, 200, 287);
    }

    function Footer() {
        // Optional: Add page number
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }
}

// Generate PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(15, 25, 15);
$pdf->SetAutoPageBreak(true, 25);

// Logo
$logoPath = 'src/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 8, 20);
}

// Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(10, 10);
$pdf->Cell(0, 10, $title, 0, 1, 'C');

// Created on (top-right)
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetXY(-60, 10); // 60mm from the right
$pdf->Cell(50, 10, 'Created on: ' . $created_at, 0, 0, 'R');

// Line below title
$pdf->SetLineWidth(1);
$pdf->Line(15, 30, 195, 30);

// Description
$pdf->SetXY(15, 30);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, $description, 0, 'J');

// Output file
$filename = 'Snap_' . $username . '_' . $note_id . '.pdf';
$pdf->Output('D', $filename);
$conn->close();
?>
