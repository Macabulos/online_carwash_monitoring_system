<?php
session_start();
require_once '../connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];
    $base_price = $_POST['base_price'];
    
    // Handle file upload
    $target_dir = "../uploads/services/";
    $target_file = $target_dir . basename($_FILES["service_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_path = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_path)) {
        $stmt = $conn->prepare("INSERT INTO service (ServiceName, Description, ImagePath, BasePrice) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $service_name, $description, $new_filename, $base_price);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Service added successfully!";
        } else {
            $_SESSION['error_message'] = "Error adding service.";
        }
    } else {
        $_SESSION['error_message'] = "Error uploading image.";
    }
    
    header("Location: manage_services.php");
    exit();
}