<?php
session_start();
require '../connection/conn.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $age = $_POST['age'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $entered_code = $_POST['verification_code'] ?? '';


if (!isset($_SESSION['code']) || $_SESSION['code'] != $entered_code || $_SESSION['code_email'] != $email) {
    $_SESSION['error'] = "Invalid or missing verification code!";
    header("Location: register.php");
    exit();
}


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

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM customer WHERE EmailAddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['is_google_user'] == 1) {
            // Email was registered via Google -> allow manual password setup
        } else {
            $_SESSION['error'] = "This email is already registered manually!";
            header("Location: register.php");
            exit();
        }
    }

    // Check if username is already taken
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

    if (isset($user)) {
        // Update the existing Google user with password
        $stmt = $conn->prepare("UPDATE customer SET Username = ?, Age = ?, Password = ?, is_google_user = 0 WHERE EmailAddress = ?");
        $stmt->bind_param("siss", $username, $age, $hashed_password, $email);
    } else {
        // Normal new registration
        $stmt = $conn->prepare("INSERT INTO customer (Username, EmailAddress, Age, Password, is_google_user) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("ssis", $username, $email, $age, $hashed_password);
    }
    $stmt->execute();

    $_SESSION['success'] = "Registered successfully! Please login.";
    header("Location: register.php");
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
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Username" name="username" required>
        </div>
        <div class="mb-3">
        <input type="email" class="form-control me-2" placeholder="Enter Gmail" name="email" id="emailInput" required>
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
        <!-- Send Code Button -->
        <div class="mb-3 d-flex">
     
            <button type="button" class="btn login-btn" onclick="sendCode()">Send Code</button>
        </div>

        <!-- Verification Code Field -->
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Enter Verification Code" name="verification_code" required>
        </div>

        <button type="submit" name="register" class="btn login-btn">Register</button>

        <!-- Go back to login link -->
        <div class="text-center mt-3">
            <p>Already have an account? <a href="login.php" class="text-decoration-none">Go back to Login</a></p>
        </div>
    </form>
</div>

<!-- <script src="./js/login.js"></script> -->
<script src="./js/register.js">
</script>
<script>
function sendCode() {
    const email = document.getElementById('emailInput').value;
    if (!email) {
        alert("Please enter your Gmail first.");
        return;
    }

    const form = new FormData();
    form.append('email', email);

    fetch('send_code.php', {
        method: 'POST',
        body: form
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert("Verification code sent to your email.");
        } else {
            alert(data.message || "Failed to send code.");
        }
    })
    .catch(error => {
        alert("Error sending code.");
        console.error(error);
    });
}

</script>


</body>
</html>   