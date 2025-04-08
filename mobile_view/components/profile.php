<?php
session_start();
include '../../connection/conn.php';

if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$CustomerID = $_SESSION['CustomerID'];
$result = mysqli_query($conn, "SELECT * FROM customer WHERE CustomerID = $CustomerID");
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<body>

<?php include './semantic/navbar.php'; ?>

<section id="profile" class="container mt-5">
    <h2>Edit Profile</h2>

    <?php
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>{$_SESSION['success']}</div>";
        unset($_SESSION['success']);
    } elseif (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    ?>

    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
        <label>Username:</label>
        <input type="text" class="form-control mb-3" name="Username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>

        <label>Email:</label>
        <input type="email" class="form-control mb-3" name="email" value="<?php echo htmlspecialchars($user['EmailAddress']); ?>" required>

        <label>Age:</label>
        <input type="number" class="form-control mb-3" name="age" value="<?php echo htmlspecialchars($user['Age']); ?>">

        <label>Profile Picture:</label>
        <input type="file" class="form-control mb-3" name="profile_picture">

        <?php if (!empty($user['ProfilePicture'])): ?>
            <img src="<?php echo $user['ProfilePicture']; ?>" alt="Profile Picture" style="max-width: 150px;" class="mb-3 d-block">
        <?php endif; ?>

        <button type="submit" class="btn btn-success">Update Profile</button>
    </form>
</section>

</body>
</html>
