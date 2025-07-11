<?php
session_start();
require_once '../connection/conn.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Get month/year from POST
$month = $_POST['month'] ?? null;
$year = $_POST['year'] ?? null;

if (!$month || !$year) {
    $_SESSION['error_message'] = "Invalid month/year specified.";
    header("Location: activity_report.php");
    exit;
}

// Delete bookings for the specified month/year
$query = "DELETE FROM bookings WHERE MONTH(BookingDate) = ? AND YEAR(BookingDate) = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $month, $year);

if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;
    $_SESSION['success_message'] = "Successfully deleted $affected_rows records for " . date('F Y', mktime(0, 0, 0, $month, 1, $year));
} else {
    $_SESSION['error_message'] = "Error deleting records: " . $conn->error;
}

$stmt->close();
header("Location: activity_report.php?month=$month&year=$year");
exit;
?>