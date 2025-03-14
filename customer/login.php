<?php
session_start();
include '../connection/conn.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Updated table name to `customer` and corrected column names
    $sql = "SELECT * FROM customer WHERE EmailAddress = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user['Password'])) {
            $_SESSION['customer_id'] = $user['CustomerID']; // Use correct ID field
            $_SESSION['username'] = $user['Username']; // Store username in session
            header("Location: ./dashboard/dashboard.php");
            exit;
        } else {
            echo "<script>alert('Invalid password!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found!'); window.location.href='login.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Duck’z Auto Detailing</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="split-container">
    <div class="left-side"></div>
    <div class="right-side">
      <div class="container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
          <input type="email" name="email" placeholder="Email" required>

          <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <i class="fas fa-eye" id="togglePassword"></i>
          </div>

          <button type="submit">Login</button>
        </form>
        <div class="loading" style="display: none;">
          <div class="loading-spinner"></div>
        </div>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
      </div>
    </div>
  </div>

  <script src="js/index.js"></script>
</body>
</html>
