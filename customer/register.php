<?php
include '../connection/conn.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $age = $_POST['age'];
    $password = $_POST['password'];
    $c_password = $_POST['c_password'];

    if ($password !== $c_password) {
        echo "Passwords do not match!";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Hash password

    // Use prepared statements to prevent SQL injection
    $sql = "INSERT INTO customer (Username, EmailAddress, Age, Password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $username, $email, $age, $hashed_password);

    if ($stmt->execute()) {
        echo "Registration successful! <a href='login.php'>Login here</a>";
        header("location: login.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Duck’z Auto Detailing</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="split-container">
    <div class="left-side"></div>
    <div class="right-side">
      <div class="container">
        <h2>Register</h2>
        <form action="register.php" method="POST">
          <input type="text" name="username" placeholder="User Name" required>
          <input type="email" name="email" placeholder="Email" required>
          <input type="text" name="address" placeholder="Address" required>
          <input type="number" name="age" placeholder="Age" required>

          <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <i class="fas fa-eye" id="togglePassword"></i>
          </div>

          <div class="password-wrapper">
            <input type="password" name="c_password" id="c_password" placeholder="Confirm Password" required>
            <i class="fas fa-eye" id="toggleConfirmPassword"></i>
          </div>

          <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
      </div>
    </div>
  </div>

  <script src="js/index.js"></script>
</body>
</html>
