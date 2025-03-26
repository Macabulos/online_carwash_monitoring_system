<?php
session_start();
require_once '../connection/conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['customer_id'], $_POST['status_id'])) {
        echo "Missing required fields.";
        exit;
    }

    $customerID = intval($_POST['customer_id']);
    $statusID = intval($_POST['status_id']);

    // Validate input
    if (empty($customerID) || empty($statusID)) {
        echo "Invalid input.";
        exit;
    }

    // Update only the StatusID
    $sql = "UPDATE customer SET StatusID = ? WHERE CustomerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $statusID, $customerID);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating Booking.";
    }

    $stmt->close();
}
?>
