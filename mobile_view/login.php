<?php
session_start();
require '../connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in both fields!";
        header("Location: login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT CustomerID, Username, Password, Status FROM customer WHERE EmailAddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // 🔒 Check if account is blocked
        if ($user['Status'] === 'Blocked') {
            $_SESSION['error'] = "Your account is blocked. Please contact admin.";
            header("Location: login.php");
            exit();
        }

        if (password_verify($password, $user['Password'])) {
            session_regenerate_id(true);
            $_SESSION['CustomerID'] = $user['CustomerID'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['success'] = "Successfully logged in! Redirecting...";
            header("Refresh: 1; URL=./components/dashboard.php");
        } else {
            $_SESSION['error'] = "Invalid password!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "User not found!";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<div class="form-container">
    <h2>Login</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success" role="alert">' . $_SESSION['success'] . '</div>';
        echo '<p id="countdown" class="text-muted"></p>';
        unset($_SESSION['success']);
    }
    ?>

    <form method="POST" action="" id="loginForm">
        <div class="mb-3">
            <input type="email" class="form-control" placeholder="Email" name="email" id="email" required>
        </div>
        <div class="mb-3 position-relative">
            <input type="password" class="form-control" placeholder="Password" name="password" id="password" required>
            <i class="fas fa-eye" id="togglePassword"></i>
        </div>
        <button type="submit" name="login" class="btn login-btn">Login</button>
    </form>

    <!-- OR Divider -->
    <!-- <div class="divider">
        <span>Or Register</span>
    </div> -->
    <div class="text-center mt-2">
    <p><a href="forgot_password.php" class="text-decoration-none text-danger">Forgot your password?</a></p>
         <!-- <small class="text-muted">Please contact the admin to reset your account.</small> -->
    </div>

    <!-- Register Button Only -->
    <div class="text-center mt-3">
        <p>Don't have an account? <a href="register.php" class="text-decoration-none">Register</a></p>
    </div>

</div>

<script src="./js/login.js"></script>
</body>
</html>
