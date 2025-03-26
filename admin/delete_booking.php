<?php
require_once '../connection/conn.php'; // Include database connection

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if ID is provided and is numeric
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $customerID = intval($_POST['id']);

        // Prepare DELETE statement
        $stmt = $conn->prepare("DELETE FROM customer WHERE CustomerID = ?");
        $stmt->bind_param("i", $customerID);

        if ($stmt->execute()) {
            echo "success"; // This will be read by JavaScript to confirm deletion
        } else {
            echo "error: " . $stmt->error; // Return error message if deletion fails
        }

        $stmt->close();
    } else {
        echo "error: Invalid booking ID";
    }
} else {
    echo "error: Invalid request method";
}

$conn->close();
?>
