<?php
session_start();
require_once '../../connection/conn.php';

// Ensure the user is logged in as a customer
if (!isset($_SESSION['customer_id']) || empty($_SESSION['customer_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../../auth/login.php");
    exit;
}

$customer_id = intval($_SESSION['customer_id']); // Secure customer ID

// Fetch booking details with service name and status name
$query = "SELECT c.ServiceID, s.ServiceName, c.BookingDate, st.StatusName
          FROM customer c
          LEFT JOIN service s ON c.ServiceID = s.ServiceID
          LEFT JOIN status st ON c.StatusID = st.StatusID
          WHERE c.CustomerID = ? AND c.ServiceID IS NOT NULL
          ORDER BY c.BookingDate DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<?php include './components/header.php'; ?>
<body>
<?php include './components/sidebar.php'; ?>
<div class="content">
    <?php include './components/navbar.php'; ?>
    <h2>Your Booking Status</h2>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert error"> 
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?> 
        </div>
    <?php endif; ?>

    <div class="dashboard-cards">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($booking = $result->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($booking['ServiceName'] ?? 'No Service'); ?></h3>
                    <p><strong>Date:</strong> 
                        <?php echo $booking['BookingDate'] ? date('F j, Y, g:i A', strtotime($booking['BookingDate'])) : 'Not Set'; ?>
                    </p>
                    <p><strong>Status:</strong> 
                        <span class="status <?php echo strtolower($booking['StatusName'] ?? 'pending'); ?>">
                            <?php echo htmlspecialchars($booking['StatusName'] ?? 'Pending'); ?>
                        </span>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-bookings">You have no bookings yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
<script src="./js/dash.js"></script>
</html>
