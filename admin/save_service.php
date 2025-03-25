<?php
include '../connection/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];

    $sql = "INSERT INTO service (ServiceName, Description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $service_name, $description);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Service added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding service.";
    }
    header("Location: manage_services.php");
    exit();
}
?>
