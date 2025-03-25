<?php
session_start();
require_once '../../connection/conn.php';

// Ensure only logged-in customers can access
if (!isset($_SESSION['customer_id']) || empty($_SESSION['customer_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../../auth/login.php");
    exit;
}

$customer_id = intval($_SESSION['customer_id']); // Secure customer ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = (int)$_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $status = "Pending";
    $datetime = $booking_date . ' ' . $booking_time;

    // Check if customer already has a booking
    $check_query = "SELECT ServiceID FROM customer WHERE CustomerID = ? AND ServiceID IS NOT NULL";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("i", $customer_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $_SESSION['error_message'] = "You already have an active booking!";
    } else {
        // Update customer table with new booking details
        $stmt = $conn->prepare("UPDATE customer SET ServiceID = ?, BookingDate = ?, Status = ? WHERE CustomerID = ?");
        $stmt->bind_param("issi", $service_id, $datetime, $status, $customer_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Booking successfully created!";
        } else {
            $_SESSION['error_message'] = "Error booking your service. Please try again.";
        }
        $stmt->close();
    }
    $stmt_check->close();
}

// Fetch available services
$services = $conn->query("SELECT * FROM service");
?>

<?php include './components/header.php'; ?>
<body>
<?php include './components/sidebar.php'; ?>
<div class="content">
    <?php include './components/navbar.php'; ?>
    <h2>Book Your Service</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="alert error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="booking-container">
        <form action="booking.php" method="POST">
            <label for="service_id">Select Service:</label>
            <select id="service_id" name="service_id" required>
                <?php while ($service = $services->fetch_assoc()): ?>
                    <option value="<?php echo $service['ServiceID']; ?>"><?php echo $service['ServiceName']; ?></option>
                <?php endwhile; ?>
            </select>

            <label for="booking_date">Booking Date:</label>
            <input type="date" id="booking_date" name="booking_date" required>

            <label for="booking_time">Booking Time:</label>
            <input type="time" id="booking_time" name="booking_time" required>

            <button type="submit">Submit Booking</button>
        </form>
    </div>
</div>
</body>
<script src="./js/dash.js"></script>
</html>
