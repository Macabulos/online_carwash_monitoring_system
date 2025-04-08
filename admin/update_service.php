<?php
session_start(); // Make sure session is started for messages
include '../connection/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = $_POST['service_id'];
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];

    // Start by checking if a new image was uploaded
    $image_path = null;
    if (!empty($_FILES['service_image']['name'])) {
        $target_dir = "../uploads/services/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $image_name = basename($_FILES["service_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $image_name;

        if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
            $image_path = basename($target_file); // only store filename
        }
    }

    // Update with or without image
    if ($image_path) {
        $sql = "UPDATE service SET ServiceName = ?, Description = ?, ImagePath = ? WHERE ServiceID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $service_name, $description, $image_path, $service_id);
    } else {
        $sql = "UPDATE service SET ServiceName = ?, Description = ? WHERE ServiceID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $service_name, $description, $service_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Service updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating service.";
    }

    header("Location: manage_services.php");
    exit();
}
?>
