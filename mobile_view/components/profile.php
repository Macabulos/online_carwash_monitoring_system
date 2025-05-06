<?php
session_start();
require_once '../../connection/conn.php'; // update path as needed

// Assuming customer is logged in and their ID is stored in session
$customerID = $_SESSION['CustomerID'];
// Replace with: $_SESSION['customer_id'] if sessions are working
if (isset($_SESSION['CustomerID'])) {
  $customerID = $_SESSION['CustomerID'];
  $result = mysqli_query($conn, "SELECT * FROM customer WHERE CustomerID = $customerID");
  $customer = mysqli_fetch_assoc($result);
}
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Handle profile picture upload
    if ($_FILES['profile_picture']['name']) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $updatePic = ", ProfilePicture = '$target_file'";
    } else {
        $updatePic = "";
    }

    // Build update query
    $sql = "UPDATE customer SET Username = '$username', EmailAddress = '$email', Age = $age $updatePic";
    if ($password) {
        $sql .= ", Password = '$password'";
    }
    $sql .= " WHERE CustomerID = $customerID";

    if (mysqli_query($conn, $sql)) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile: " . mysqli_error($conn);
    }
}

// Fetch current customer data
$result = mysqli_query($conn, "SELECT * FROM customer WHERE CustomerID = $customerID");
$customer = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<?php include './semantic/navbar.php'; ?>
<body class="bg-light">
<div class="container py-5">
  <h2>Edit Profile</h2>
  <!-- <?php if (isset($message)) echo "<div class='alert alert-info'>$message</div>"; ?> -->
  <form action="" method="POST" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm">

    <div class="mb-3">
      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($customer['Username']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Email Address</label>
      <input type="email" name="email" value="<?= htmlspecialchars($customer['EmailAddress']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Age</label>
      <input type="number" name="age" value="<?= htmlspecialchars($customer['Age']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>New Password <small>(Leave blank to keep current password)</small></label>
      <input type="password" name="password" class="form-control">
    </div>

    <div class="mb-3">
      <label>Profile Picture</label><br>
      <?php if ($customer['ProfilePicture']): ?>
        <img src="<?= $customer['ProfilePicture'] ?>" width="100" class="img-thumbnail mb-2"><br>
      <?php endif; ?>
      <input type="file" name="profile_picture" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Update Profile</button>
    <a href="./dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

</body>
</html>
