<?php
require_once '../connection/conn.php'; // Database connection


// Fetch current admin details
$query = "SELECT Email, ProfilePicture FROM admin WHERE AdminID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_email = $_POST['email'];

        $update_query = "UPDATE admin SET Email = ? WHERE AdminID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_email, $admin_id);
        $stmt->execute();
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: admin_profile.php");
        exit();
    }

    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        // Verify current password
        $check_query = "SELECT Password FROM admin WHERE AdminID = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_data = $result->fetch_assoc();

        if (password_verify($current_password, $admin_data['Password'])) {
            $update_password_query = "UPDATE admin SET Password = ? WHERE AdminID = ?";
            $stmt = $conn->prepare($update_password_query);
            $stmt->bind_param("si", $new_password, $admin_id);
            $stmt->execute();
            $_SESSION['success_message'] = "Password changed successfully!";
        } else {
            $_SESSION['error_message'] = "Current password is incorrect!";
        }
        header("Location: admin_profile.php");
        exit();
    }

    // Upload profile picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . time() . "_" . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $update_picture_query = "UPDATE admin SET ProfilePicture = ? WHERE AdminID = ?";
            $stmt = $conn->prepare($update_picture_query);
            $stmt->bind_param("si", $target_file, $admin_id);
            $stmt->execute();
            $_SESSION['success_message'] = "Profile picture updated!";
        } else {
            $_SESSION['error_message'] = "Error uploading profile picture!";
        }
        header("Location: admin_profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Profile</title>
    <?php include 'includes/head.php'; ?>
 
</head>
<style>
    
</style>
<body>
<?php include 'includes/nav.php'; ?>
<div class="main">
    <?php include 'includes/navtop.php'; ?>
    <main class="content">
        <div class="container">
            <h2>Admin Profile</h2>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="alert error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <!-- Profile Picture Display -->
            <div class="profile-pic-container">
                <img src="<?php echo !empty($admin['ProfilePicture']) ? $admin['ProfilePicture'] : 'uploads/default.png'; ?>" class="profile-pic" alt="Profile Picture">
            </div>

            <form action="admin_profile.php" method="POST">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($admin['Email']); ?>" required>
                <button type="submit" name="update_profile">Update Profile</button>
            </form>

            <form action="admin_profile.php" method="POST">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>
                <label>New Password:</label>
                <input type="password" name="new_password" required>
                <button type="submit" name="change_password">Change Password</button>
            </form>

            <form action="admin_profile.php" method="POST" enctype="multipart/form-data">
                <label>Profile Picture:</label>
                <input type="file" name="profile_picture">
                <button type="submit">Upload</button>
            </form>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
