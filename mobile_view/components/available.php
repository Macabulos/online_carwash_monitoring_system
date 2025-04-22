<?php
session_start();
require '../../connection/conn.php';
// Redirect if the user is not logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}
$CustomerID = $_SESSION['CustomerID'];
// Fetch available bookings (services)
$bookings = $conn->query("SELECT ServiceID, ServiceName, Description, ImagePath FROM service");
?>
<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<body>
<!-- Navbar -->
<?php include './semantic/navbar.php'; ?>
<!-- Dashboard: Available Bookings -->
<main class="container mt-4">
    <section id="dashboard">
        <h2>Available Bookings</h2>
        <div class="row">
            <?php 
            if ($bookings->num_rows > 0) {
                while ($row = $bookings->fetch_assoc()) {
                    $serviceName = htmlspecialchars($row['ServiceName']);
                    $description = htmlspecialchars($row['Description']);
                    $imagePath = !empty($row['ImagePath']) ? '../../uploads/services/' . htmlspecialchars($row['ImagePath']) : 'images/default.jpg';
            ?>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="Service Image" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $serviceName; ?></h5>
                            <p class="card-text" title="<?php echo $description; ?>">
                                <?php echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='text-muted'>No available bookings at the moment.</p>";
            }
            ?>
        </div>
    </section>
</main>
</body>
</html>
