<?php
session_start();
require_once '../../connection/conn.php'; // update path as needed

// Assuming admin is logged in and their ID is stored in session
$adminID = 1; // Replace this with: $_SESSION['admin_id']; if session-based login is active

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
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

    $sql = "UPDATE admin SET Email = '$email' $updatePic";
    if ($password) {
        $sql .= ", Password = '$password'";
    }
    $sql .= " WHERE AdminID = $adminID";

    if (mysqli_query($conn, $sql)) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile: " . mysqli_error($conn);
    }
}

// Fetch current admin data
$result = mysqli_query($conn, "SELECT * FROM admin WHERE AdminID = $adminID");
$admin = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<!-- <head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head> -->
<?php include './semantic/head.php'; ?>
<?php include './semantic/navbar.php'; ?>
<body class="bg-light">
<div class="container py-5">
  <h2>Edit Profile</h2>
  <?php if (isset($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
  <form action="" method="POST" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm">
    <div class="mb-3">
      <label>Email Address</label>
      <input type="email" name="email" value="<?= htmlspecialchars($admin['Email']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>New Password <small>(Leave blank to keep current password)</small></label>
      <input type="password" name="password" class="form-control">
    </div>

    <div class="mb-3">
      <label>Profile Picture</label><br>
      <?php if ($admin['ProfilePicture']): ?>
        <img src="<?= $admin['ProfilePicture'] ?>" width="100" class="img-thumbnail mb-2"><br>
      <?php endif; ?>
      <input type="file" name="profile_picture" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Update Profile</button>
    <a href="./dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
