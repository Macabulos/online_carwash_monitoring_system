<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if $conn is already available to avoid re-including it
if (!isset($conn)) {
    // Adjust path to the actual root
    require_once __DIR__ . '/../../connection/conn.php';
}

if (!isset($customer) && isset($_SESSION['CustomerID'])) {
    $customerID = intval($_SESSION['CustomerID']);
    $result = mysqli_query($conn, "SELECT * FROM customer WHERE CustomerID = $customerID");

    if ($result && mysqli_num_rows($result) > 0) {
        $customer = mysqli_fetch_assoc($result);
    }
}
?>
