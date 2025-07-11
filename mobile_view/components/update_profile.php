<?php
session_start();
include '../../connection/conn.php';

if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$CustomerID = $_SESSION['CustomerID'];
$Username = $_POST['Username'] ?? '';
$Email = $_POST['EmailAddress'] ?? '';
$Age = isset($_POST['age']) ? (int) $_POST['age'] : null;

$profile_picture = null;
if (!empty($_FILES['profile_picture']['name'])) {
    $targetDir = "uploads/";
    $fileName = basename($_FILES["profile_picture"]["name"]);
    $fileName = preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);
    $targetFilePath = $targetDir . time() . "_" . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
            $profile_picture = $targetFilePath;
        } else {
            $_SESSION['error'] = "Failed to upload profile picture.";
            header("Location: profile.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        header("Location: profile.php");
        exit();
    }
}

$sql = "UPDATE customer SET Username = ?, EmailAddressAddress = ?, Age = ?" . ($profile_picture ? ", ProfilePicture = ?" : "") . " WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);

if ($profile_picture) {
    $stmt->bind_param("ssisi", $Username, $EmailAddress, $Age, $profile_picture, $CustomerID);
} else {
    $stmt->bind_param("ssii", $Username, $EmailAddress, $Age, $CustomerID);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Profile updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update profile.";
}

header("Location: profile.php");
exit();
?>
