<?php
session_start();
require_once '../../connection/conn.php'; // Database connection

// Ensure the user is logged in
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['error_message'] = "Please log in to view your bookings.";
    header("Location: ../auth/login.php");
    exit;
}

$customer_id = $_SESSION['customer_id']; // Get logged-in user ID

// Fetch bookings for the logged-in customer
$query = "SELECT b.BookingID, s.ServiceName, b.Date, b.Status FROM booking b 
          JOIN service s ON b.ServiceID = s.ServiceID 
          WHERE b.CustomerID = ? ORDER BY b.Date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<?php include './components/header.php' ?>
<body>
<?php include './components/sidebar.php' ?>
<div class="content">
    <?php include './components/navbar.php' ?>
    <h2>Your Booking Status</h2>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert error"> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?> </div>
    <?php endif; ?>

    <div class="dashboard-cards">
        <?php while ($booking = $result->fetch_assoc()): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($booking['ServiceName']); ?></h3>
                <p><strong>Date:</strong> <?php echo date('F j, Y, g:i A', strtotime($booking['Date'])); ?></p>
                <p><strong>Status:</strong> <span class="status <?php echo strtolower($booking['Status']); ?>">
                    <?php echo htmlspecialchars($booking['Status']); ?></span></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
<script src="./js/dash.js"></script>
</html>
