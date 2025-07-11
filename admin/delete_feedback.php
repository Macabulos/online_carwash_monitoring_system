<?php
session_start();
require_once '../connection/conn.php';

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['feedback_id'])) {
    $feedback_id = intval($_POST['feedback_id']);

    $sql = "DELETE FROM feedback WHERE FeedbackID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $feedback_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Feedback deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete feedback.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = "Database error.";
    }
}

header("Location: manage_feedback.php"); // Redirect back after deletion
exit;
?>
