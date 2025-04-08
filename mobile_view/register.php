<?php
session_start();
require '../connection/conn.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $age = $_POST['age'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 8 || !preg_match("/[0-9]/", $password) || !preg_match("/[a-zA-Z]/", $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long and contain both letters and numbers!";
        header("Location: register.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM customer WHERE EmailAddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email is already registered!";
        header("Location: register.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM customer WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username is already taken!";
        header("Location: register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO customer (Username, EmailAddress, Age, Password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $username, $email, $age, $hashed_password);
    $stmt->execute();

    $_SESSION['success'] = "Registered successfully! Please login.";
    header("Location: login.php");
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
        <!-- Error message -->
        <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <form method="POST" action="" id="registerForm" onsubmit="return validateForm()">
        <!-- form fields here (same as yours) -->
    <form method="POST" action="" id="registerForm" onsubmit="return validateForm()">
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Username" name="username" required>
        </div>
        <div class="mb-3">
            <input type="email" class="form-control" placeholder="Email" name="email" required>
        </div>
        <div class="mb-3">
            <input type="number" class="form-control" placeholder="Age" name="age">
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" placeholder="Password" id="password" name="password" required>
            <i class="fas fa-eye" id="togglePassword"></i>
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" placeholder="Confirm Password" id="confirm_password" name="confirm_password" required>
            <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
        </div>
        <button type="submit" name="register" class="btn btn-primary">Register</button>
    </form>
</div>

<!-- <script src="./js/login.js"></script> -->
<script>
// Toggle password visibility
document.getElementById("togglePassword").addEventListener("click", function() {
    const passwordField = document.getElementById("password");
    const type = passwordField.type === "password" ? "text" : "password";
    passwordField.type = type;
    this.classList.toggle("fa-eye-slash");
});

document.getElementById("toggleConfirmPassword").addEventListener("click", function() {
    const confirmPasswordField = document.getElementById("confirm_password");
    const type = confirmPasswordField.type === "password" ? "text" : "password";
    confirmPasswordField.type = type;
    this.classList.toggle("fa-eye-slash");
});

// Client-side validation
function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false;
    }
    
    if (password.length < 8 || !/[a-zA-Z]/.test(password) || !/[0-9]/.test(password)) {
        alert("Password must be at least 8 characters long and contain both letters and numbers.");
        return false;
    }

    return true;
}
</script>
</body>
</html>
