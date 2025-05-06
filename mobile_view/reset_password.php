<?php
session_start();
require '../connection/conn.php';

if (!isset($_SESSION['reset_email'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
    } elseif (strlen($new_password) < 8 || !preg_match("/[A-Za-z]/", $new_password) || !preg_match("/[0-9]/", $new_password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long and contain both letters and numbers!";
    } else {
        $email = $_SESSION['reset_email'];
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("UPDATE customer SET Password = ? WHERE EmailAddress = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            unset($_SESSION['reset_email']);
            $_SESSION['success'] = "Password has been reset. Please log in.";
            header("Location: reset_password.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to reset password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    

    <!-- Inline Style (same from login/register page) -->
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
        }

        .form-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .btn {
            width: 100%;
        }

        .mb-3 {
            position: relative;
        }

        #toggleNewPassword, #toggleConfirmPassword {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 1.2rem;
        }

        input[type="password"] {
            padding-right: 30px;
        }

        #toggleNewPassword:hover, #toggleConfirmPassword:hover {
            color: #007bff;
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
    </style>
</head>
<body>
<div class="form-container">
    <h2>Reset Password</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="reset_password.php">
        <div class="mb-3">
            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="New Password" required>
            <i class="fas fa-eye" id="toggleNewPassword"></i>
        </div>
        <div class="mb-3">
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required>
            <i class="fas fa-eye" id="toggleConfirmPassword"></i>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>

    <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">Back to Login</a>
    </div>
</div>

<!-- Password Toggle Script -->
<script>
    document.getElementById('toggleNewPassword').addEventListener('click', function () {
        const input = document.getElementById('new_password');
        this.classList.toggle('fa-eye-slash');
        input.type = input.type === 'password' ? 'text' : 'password';
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
        const input = document.getElementById('confirm_password');
        this.classList.toggle('fa-eye-slash');
        input.type = input.type === 'password' ? 'text' : 'password';
    });
</script>
</body>
</html>
