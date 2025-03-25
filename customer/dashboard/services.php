<?php
session_start();
require_once '../../connection/conn.php'; // Database connection

// Ensure the user is logged in as a customer
if (!isset($_SESSION['customer_id']) || empty($_SESSION['customer_id'])) {
    $_SESSION['error_message'] = "Please log in to view available services.";
    header("Location: ../../auth/login.php");
    exit;
}

$customer_id = intval($_SESSION['customer_id']); // Secure user ID

// Fetch available services
$query = "SELECT * FROM service";
$result = $conn->query($query);
?>

<?php include './components/header.php'; ?>
<body>
<?php include './components/sidebar.php'; ?>
<div class="content">
    <?php include './components/navbar.php'; ?>
    <h2>Available Services</h2>

    <div class="service-cards">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($service = $result->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($service['ServiceName']); ?></h3>
                    <p><?php echo htmlspecialchars($service['Description']); ?></p>
                    <a href="booking.php?service_id=<?php echo $service['ServiceID']; ?>" class="book-btn">Book Now</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No services available at the moment.</p>
        <?php endif; ?>
    </div>
</div>
</body>
<script src="./js/dash.js"></script>
</html>
