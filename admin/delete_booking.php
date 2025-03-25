<?php
require_once '../connection/conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $customerID = intval($_POST['id']);

    $stmt = $conn->prepare("UPDATE customer SET ServiceID = NULL, BookingDate = NULL, Status = NULL WHERE CustomerID = ?");
    $stmt->bind_param("i", $customerID);

    if ($stmt->execute()) {
        echo "Booking deleted successfully!";
    } else {
        echo "Error deleting booking.";
    }
    $stmt->close();
}
?>
