<?php
include '../connection/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];

    // Handle image upload
    $image_path = null;
    if (!empty($_FILES['service_image']['name'])) {
        $target_dir = "../uploads/services/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $image_name = basename($_FILES["service_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $image_name;

        if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
            $image_path = basename($target_file); // Only store relative path
        }
    }

    $sql = "INSERT INTO service (ServiceName, Description, ImagePath) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $service_name, $description, $image_path);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Service added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding service.";
    }

    header("Location: manage_services.php");
    exit();
}
?>
