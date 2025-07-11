<?php
session_start();
require '../../connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $CustomerID = $_POST['CustomerID'];
    $ServiceID = $_POST['ServiceID'];
    $Comments = $conn->real_escape_string($_POST['Comments']);
    $Ratings = (int)$_POST['Ratings'];

    // Insert into feedback (with ServiceID)
    $sql = "INSERT INTO feedback (CustomerID, ServiceID, Comments, Ratings) 
            VALUES ('$CustomerID', '$ServiceID', '$Comments', '$Ratings')";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Thank you for your feedback!";
    } else {
        $_SESSION['error'] = "Error submitting feedback.";
    }
    
    header("Location: ./available.php"); // Redirect back to available bookings page
    exit();
}
?>
