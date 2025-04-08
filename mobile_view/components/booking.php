<?php
session_start();
require '../../connection/conn.php';

// Check if user is logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$CustomerID = $_SESSION['CustomerID'];

// Fetch all available services
$bookings = $conn->query("SELECT ServiceID, ServiceName FROM service");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;
    $booking_time = $_POST['booking_time'] ?? null;

    if ($service_id && $booking_date && $booking_time) {
        $booking_datetime = $booking_date . ' ' . $booking_time;
        $status_id = 1; // default to "Pending"

        // Check if the customer already has a booking for this service at the same time
        $check_query = "SELECT * FROM bookings WHERE CustomerID = ? AND ServiceID = ? AND BookingDate = ?";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bind_param("iis", $CustomerID, $service_id, $booking_datetime);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // If booking exists, show error message
            echo "<div class='alert alert-warning'>You have already booked this service at this time.</div>";
        } else {
            // If no existing booking, proceed to insert the new booking
            $stmt = $conn->prepare("INSERT INTO bookings (CustomerID, ServiceID, BookingDate, StatusID) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $CustomerID, $service_id, $booking_datetime, $status_id);

            if ($stmt->execute()) {
                header("Location: available.php?status=success");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Error: Could not book the service. Please try again.</div>";
            }
        }
    } else {
        echo "<div class='alert alert-warning'>Please fill in all fields (Service, Date, Time).</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<body>
<?php include './semantic/navbar.php'; ?>

<div class="container mt-5">
    <h3 class="mb-4">Book a Car Wash Service</h3>

    <form method="POST" action="booking.php">
        <div class="mb-3">
            <label for="service" class="form-label">Select Service:</label>
            <select name="service" class="form-control" required>
                <option value="">-- Choose a Service --</option>
                <?php while ($row = $bookings->fetch_assoc()): ?>
                    <option value="<?= $row['ServiceID']; ?>">
                        <?= htmlspecialchars($row['ServiceName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="booking_date" class="form-label">Choose Date:</label>
            <input type="date" name="booking_date" class="form-control" required min="<?= date('Y-m-d'); ?>">
        </div>

        <div class="mb-3">
            <label for="booking_time" class="form-label">Choose Time:</label>
            <input type="time" name="booking_time" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Book Now</button>
    </form>
</div>
</body>
</html>
