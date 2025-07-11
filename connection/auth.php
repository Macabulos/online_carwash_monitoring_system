<?php
session_start();
require_once '../connection/conn.php'; // Database connection

// Check if the user is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: ../admin/dashboard.php");
    exit;
}
if (isset($_SESSION['customer_id'])) {
    header("Location: ../customer/home.php");
    exit;
}

// Handle Login Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both email and password.";
        header("Location: login.php");
        exit;
    }

    // Check if it's an Admin
    $stmt = $conn->prepare("SELECT AdminID, Email, Password FROM admin WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $adminResult = $stmt->get_result();

    if ($adminResult->num_rows > 0) {
        $admin = $adminResult->fetch_assoc();
        if (password_verify($password, $admin['Password'])) {
            $_SESSION['admin_id'] = $admin['AdminID'];
            $_SESSION['admin_email'] = $admin['Email'];
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Incorrect password. Please try again.";
            header("Location: login.php");
            exit;
        }
    }

    // Check if it's a Customer
    $stmt = $conn->prepare("SELECT CustomerID, Username, EmailAddress, Password FROM customer WHERE EmailAddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $customerResult = $stmt->get_result();

    if ($customerResult->num_rows > 0) {
        $customer = $customerResult->fetch_assoc();
        if (password_verify($password, $customer['Password'])) {
            $_SESSION['customer_id'] = $customer['CustomerID'];
            $_SESSION['customer_name'] = $customer['Username'];
            $_SESSION['customer_email'] = $customer['EmailAddress'];
            header("Location: ../customer/home.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Incorrect password. Please try again.";
            header("Location: login.php");
            exit;
        }
    }

    // If neither Admin nor Customer is found
    $_SESSION['error_message'] = "Account not found. Please check your email and password.";
    header("Location: login.php");
    exit;
}
?>
