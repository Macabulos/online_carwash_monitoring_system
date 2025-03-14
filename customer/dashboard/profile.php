<?php
session_start();
require_once '../../connection/conn.php';

if (!isset($_SESSION['customer_id'])) {
    $_SESSION['error_message'] = "Please log in first.";
    header("Location: ../auth/login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$query = "SELECT Username, EmailAddress, ProfilePicture FROM customer WHERE CustomerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<?php include './components/header.php' ?>
<body>
<?php include './components/sidebar.php' ?>
<div class="content">
    <?php include './components/navbar.php' ?>
    
    <div class="profile-container">
        <h2>Profile Settings</h2>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <!-- Profile Picture -->
            <label for="profile_pic">
                <img src="<?php echo htmlspecialchars($user['ProfilePicture'] ?? 'default-avatar.png'); ?>" 
                     alt="Profile Picture" class="profile-avatar">
            </label>
            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" style="display: none;">

            <!-- Name & Email -->
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['EmailAddress']); ?>" required>

            <!-- Buttons -->
            <button type="submit" class="btn">Save Changes</button>
            <button type="button" class="btn btn-secondary" onclick="togglePasswordForm()">Change Password</button>
        </form>
        
        <!-- Change Password Form (Hidden Initially) -->
        <div id="passwordForm" style="display: none;">
            <form action="change_password.php" method="POST">
                <input type="password" name="current_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" class="btn">Update Password</button>
            </form>
        </div>
    </div>
</div>
<script>
function togglePasswordForm() {
    var form = document.getElementById("passwordForm");
    form.style.display = form.style.display === "none" ? "block" : "none";
}
</script>
</body>
</html>
