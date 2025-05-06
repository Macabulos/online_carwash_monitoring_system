<?php
session_start();
require '../connection/conn.php';

// Handle verification code submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $verification_code = $_POST['verification_code'];
    $email = $_SESSION['pending_email'];
    
    // Check if code is valid
    $stmt = $conn->prepare("SELECT * FROM verification_codes WHERE email = ? AND code = ? AND created_at >= NOW() - INTERVAL 10 MINUTE");
    $stmt->bind_param("ss", $email, $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Complete registration with stored data
        $username = $_SESSION['pending_username'];
        $age = $_SESSION['pending_age'];
        $password = $_SESSION['pending_password'];
        
        // Check if email exists (for Google users)
        $stmt = $conn->prepare("SELECT * FROM customer WHERE EmailAddress = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['is_google_user'] == 1) {
                // Update existing Google user
                $stmt = $conn->prepare("UPDATE customer SET Username = ?, Age = ?, Password = ?, is_google_user = 0 WHERE EmailAddress = ?");
                $stmt->bind_param("siss", $username, $age, password_hash($password, PASSWORD_BCRYPT), $email);
            } else {
                $_SESSION['error'] = "This email is already registered!";
                header("Location: register.php");
                exit();
            }
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO customer (Username, EmailAddress, Age, Password, is_google_user) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssis", $username, $email, $age, password_hash($password, PASSWORD_BCRYPT));
        }
        
        if ($stmt->execute()) {
            // Clean up session
            unset($_SESSION['pending_email']);
            unset($_SESSION['pending_username']);
            unset($_SESSION['pending_age']);
            unset($_SESSION['pending_password']);
            
            // Delete used code
            $conn->query("DELETE FROM verification_codes WHERE email = '$email'");
            
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: register.php");
            exit();
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: register.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid or expired verification code!";
        header("Location: register.php");
        exit();
    }
}

// Handle initial registration submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $age = $_POST['age'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validations
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 8 || !preg_match("/[0-9]/", $password) || !preg_match("/[a-zA-Z]/", $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long and contain both letters and numbers!";
        header("Location: register.php");
        exit();
    }

    // Check if username is taken
    $stmt = $conn->prepare("SELECT * FROM customer WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username is already taken!";
        header("Location: register.php");
        exit();
    }

    // Check if email exists (non-Google users)
    $stmt = $conn->prepare("SELECT * FROM customer WHERE EmailAddress = ? AND is_google_user = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered!";
        header("Location: register.php");
        exit();
    }

    // Store registration data in session
    $_SESSION['pending_email'] = $email;
    $_SESSION['pending_username'] = $username;
    $_SESSION['pending_age'] = $age;
    $_SESSION['pending_password'] = $password;

    // Redirect to send verification code
    header("Location: send_code.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="./css/style.css">
    <title>Register</title>
</head>
<body>
<div class="form-container">
    <h2>Register</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['verification_sent'])): ?>
        <!-- Registration Form -->
        <form method="POST" action="register.php">
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Username" name="username" required>
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" placeholder="Email" name="email" required>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" placeholder="Age" name="age">
            </div>
            <div class="mb-3 position-relative">
                <input type="password" class="form-control" placeholder="Password" id="password" name="password" required>
                <i class="fas fa-eye position-absolute" id="togglePassword" style="top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></i>
            </div>
            <div class="mb-3 position-relative">
                <input type="password" class="form-control" placeholder="Confirm Password" id="confirm_password" name="confirm_password" required>
                <i class="fas fa-eye position-absolute" id="toggleConfirmPassword" style="top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></i>
            </div>

            <button type="submit" name="register" class="btn login-btn">Register</button>
        </form>
    <?php else: ?>
        <!-- Verification Code Input -->
        <div class="alert alert-info">We've sent a verification code to your email. Please check your inbox.</div>
        
        <form method="POST" action="register.php">
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Verification Code" name="verification_code" required>
            </div>
            <button type="submit" name="verify" class="btn login-btn">Verify & Complete Registration</button>
        </form>
    <?php endif; ?>

    <div class="text-center mt-3">
        <p>Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
    </div>
</div>

<script>
document.getElementById("togglePassword").addEventListener("click", function() {
    const password = document.getElementById("password");
    password.type = password.type === "password" ? "text" : "password";
    this.classList.toggle("fa-eye-slash");
});

document.getElementById("toggleConfirmPassword").addEventListener("click", function() {
    const confirmPassword = document.getElementById("confirm_password");
    confirmPassword.type = confirmPassword.type === "password" ? "text" : "password";
    this.classList.toggle("fa-eye-slash");
});



</script>


</body>
</html>