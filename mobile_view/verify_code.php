<?php
include '../connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $input_code = $_POST['verification_code'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $age = $_POST['age'];

    // Check code
    $query = "SELECT * FROM verification_codes WHERE email='$email' AND code='$input_code' AND created_at >= NOW() - INTERVAL 10 MINUTE";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Check password match
        if ($password !== $confirm) {
            echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertUser = "INSERT INTO customer (Username, EmailAddress, age, password) 
        VALUES ('$username', '$email', '$age', '$hashedPassword')";

        if (mysqli_query($conn, $insertUser)) {
            // Delete used code
            mysqli_query($conn, "DELETE FROM verification_codes WHERE email='$email'");
            echo "<script>alert('Account created successfully!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Registration failed.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid or expired verification code.'); window.history.back();</script>";
    }
}
?>
