<?php
session_start();
require_once '../../connection/conn.php'; // Database connection

// Ensure the user is logged in
// if (!isset($_SESSION['user_id'])) {
//     $_SESSION['error_message'] = "Please log in to book a service.";
//     header("Location: ../auth/login.php");
//     exit;
// }

// $customer_id = $_SESSION['user_id']; // Get logged-in user ID

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = (int)$_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $status = "Pending";
    $datetime = $booking_date . ' ' . $booking_time;

    // Insert booking into database
    $stmt = $conn->prepare("INSERT INTO booking (CustomerID, ServiceID, Date, Status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $customer_id, $service_id, $datetime, $status);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Booking successfully created!";
    } else {
        $_SESSION['error_message'] = "Error booking your service. Please try again.";
    }
    $stmt->close();
}

// Fetch available services
$services = $conn->query("SELECT * FROM service");
?>

<?php include './components/header.php' ?>
<body>
<?php include './components/sidebar.php' ?>
<div class="content">
    <?php include './components/navbar.php' ?>
<div class="booking-container">
    <h2>Book Your Service</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="alert error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

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
