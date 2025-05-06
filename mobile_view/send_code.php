<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';

require '../connection/conn.php';

session_start();

if (!isset($_SESSION['pending_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['pending_email'];

// Generate and store verification code
$code = rand(100000, 999999);

// Delete old codes
$conn->query("DELETE FROM verification_codes WHERE email = '$email'");

// Insert new code
$stmt = $conn->prepare("INSERT INTO verification_codes (email, code, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $email, $code);
$stmt->execute();

// Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; 
    $mail->SMTPAuth = true;
    $mail->Username = 'johnlestermacabulos@gmail.com';
    $mail->Password = 'ltgz vpen ynwo gxnn';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('johnlestermacabulos@gmail.com', 'Duck\'z Auto Detailing & Car Wash');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Email Verification Code';
    $mail->Body = "<h2>Your verification code is: $code</h2>";

    $mail->send();

    $_SESSION['verification_sent'] = true;
    $_SESSION['success'] = "Verification code sent to your email!";
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to send verification code. Please try again.";
}

header("Location: register.php");
exit();
?>