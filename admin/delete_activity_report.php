<?php
session_start();
require_once '../connection/conn.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized action.";
    header("Location: ../auth/login.php");
    exit;
}

// Assuming 'report' is the table to be cleared
$deleteQuery = "DELETE FROM report";
if (mysqli_query($conn, $deleteQuery)) {
    $_SESSION['success_message'] = "All reports have been deleted.";
} else {
    $_SESSION['error_message'] = "Failed to delete reports: " . mysqli_error($conn);
}

header("Location: manage_reports.php");
exit;
?>
