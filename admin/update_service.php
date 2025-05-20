<?php
session_start();
require_once '../connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'];
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];
    $base_price = $_POST['base_price'];
    
    // Get current image path
    $current_image = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ImagePath FROM service WHERE ServiceID = $service_id"))['ImagePath'];
    $new_filename = $current_image;
    
    // Handle file upload if a new image was provided
    if (!empty($_FILES["service_image"]["name"])) {
        $target_dir = "../uploads/services/";
        $target_file = $target_dir . basename($_FILES["service_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_path = $target_dir . $new_filename;
        
        if (!move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_path)) {
            $_SESSION['error_message'] = "Error uploading image.";
            header("Location: manage_services.php");
            exit();
        }
        
        // Delete old image
        if (file_exists($target_dir . $current_image)) {
            unlink($target_dir . $current_image);
        }
    }
    
    $stmt = $conn->prepare("UPDATE service SET ServiceName = ?, Description = ?, ImagePath = ?, BasePrice = ? WHERE ServiceID = ?");
    $stmt->bind_param("sssdi", $service_name, $description, $new_filename, $base_price, $service_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Service updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating service.";
    }
    
    header("Location: manage_services.php");
    exit();
}