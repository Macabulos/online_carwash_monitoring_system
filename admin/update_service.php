<?php
include '../connection/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = $_POST['service_id'];
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];

    $sql = "UPDATE service SET ServiceName = ?, Description = ? WHERE ServiceID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $service_name, $description, $service_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Service updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating service.";
    }
    header("Location: manage_services.php");
    exit();
}
?>
