<?php
session_start();
require_once '../connection/conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT CustomerID, Username FROM customer WHERE EmailAddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        $update = $conn->prepare("UPDATE customer SET otp_code = ?, otp_expires_at = ? WHERE CustomerID = ?");
        $update->bind_param("ssi", $otp, $expiry, $user['CustomerID']);
        $update->execute();
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username = 'johnlestermacabulos@gmail.com';
            $mail->Password = 'ltgz vpen ynwo gxnn';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('your_email@gmail.com', 'Car Wash System');
            $mail->addAddress($email, $user['Username']);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = "Dear {$user['Username']},\n\nYour OTP code is: $otp\n\nIt will expire in 10 minutes.";

            $mail->send();

            $_SESSION['otp_email'] = $email;
            header("Location: verify_otp.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send OTP. Try again.";
        }
    } else {
        $_SESSION['error'] = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Optional Bootstrap for buttons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Custom CSS for responsiveness -->
    <style>
        body {
            background: 
                linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('../../img/wash.webp') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h4 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .btn {
            width: 100%;
        }

        @media (max-width: 768px) {
            .form-container {
                max-width: 90%;
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .form-container {
                max-width: 95%;
                padding: 1.2rem;
            }
        }

        .text-center a {
            color: #007bff;
            text-decoration: none;
        }

        .text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h4>Forgot Password</h4>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="email">Enter your email</label>
            <input type="email" name="email" class="form-control" required placeholder="Enter registered email">
        </div>
        <button type="submit" class="btn btn-primary">Send OTP</button>
    </form>

    <div class="mt-3 text-center">
        <a href="login.php">Back to login</a>
    </div>
</div>
</body>
</html>
