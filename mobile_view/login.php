<?php
session_start();
require '../connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate the inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in both fields!";
        header("Location: login.php");
        exit();
    }

    // Prepare SQL query to fetch user based on email
    $stmt = $conn->prepare("SELECT CustomerID, Username, Password FROM customer WHERE EmailAddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['Password'])) {
            session_regenerate_id(true); // Prevent session fixation

            // Set session variables
            $_SESSION['CustomerID'] = $user['CustomerID']; 
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['success'] = "Successfully logged in! Redirecting...";

            // Delay redirection for 5 seconds
            header("Refresh: 5; URL=./components/dashboard.php");
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

    <!-- Alert messages -->
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
        <button type="submit" name="login" class="btn btn-success">Login</button>
    </form>
</div>

<!-- JavaScript -->
<script>
// Toggle password visibility
document.getElementById("togglePassword").addEventListener("click", function() {
    const passwordField = document.getElementById("password");
    const type = passwordField.type === "password" ? "text" : "password";
    passwordField.type = type;
    this.classList.toggle("fa-eye-slash");
});

// Client-side validation
document.getElementById("loginForm").addEventListener("submit", function(event) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!email || !password) {
        event.preventDefault();
        alert("Please fill in both fields!");
    }
});

// Countdown for redirection
// let seconds = 2;
// const countdownEl = document.getElementById('countdown');
// if (countdownEl) {
//     const timer = setInterval(() => {
//         countdownEl.textContent = `Redirecting in ${seconds} second${seconds !== 1 ? 's' : ''}...`;
//         seconds--;
//         if (seconds < 0) clearInterval(timer);
//     }, 1000);
// }
</script>
</body>
</html>
