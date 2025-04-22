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
        $booking_datetime = date("Y-m-d H:i:s", strtotime("$booking_date $booking_time"));
        $status_id = 1; // "Pending"

        // ✅ Check if the user already has a future pending booking
        $existing_booking_query = "SELECT * FROM bookings 
                                   WHERE CustomerID = ? 
                                   AND BookingDate > NOW()
                                   AND StatusID = 1"; // assuming 1 = Pending
        $stmt_existing = $conn->prepare($existing_booking_query);
        $stmt_existing->bind_param("i", $CustomerID);
        $stmt_existing->execute();
        $result_existing = $stmt_existing->get_result();

        if ($result_existing->num_rows > 0) {
            echo "<div class='alert alert-warning'>You already have a pending booking. Please complete or cancel it before booking again.</div>";
        } else {
            // ✅ Check time slot conflict
            $check_conflict_query = "SELECT * FROM bookings WHERE ServiceID = ? AND BookingDate = ?";
            $stmt_conflict = $conn->prepare($check_conflict_query);
            $stmt_conflict->bind_param("is", $service_id, $booking_datetime);
            $stmt_conflict->execute();
            $result_conflict = $stmt_conflict->get_result();

            if ($result_conflict->num_rows > 0) {
                echo "<div class='alert alert-warning'>This time slot is already booked. Please choose another time.</div>";
            } else {
                // Optional: check if the customer booked this exact combo already
                $check_duplicate_query = "SELECT * FROM bookings WHERE CustomerID = ? AND ServiceID = ? AND BookingDate = ?";
                $stmt_duplicate = $conn->prepare($check_duplicate_query);
                $stmt_duplicate->bind_param("iis", $CustomerID, $service_id, $booking_datetime);
                $stmt_duplicate->execute();
                $result_duplicate = $stmt_duplicate->get_result();

                if ($result_duplicate->num_rows > 0) {
                    echo "<div class='alert alert-warning'>You already booked this service at this time.</div>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO bookings (CustomerID, ServiceID, BookingDate, StatusID) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iisi", $CustomerID, $service_id, $booking_datetime, $status_id);

                    if ($stmt->execute()) {
                        header("Location: dashboard.php?status=success");
                        exit();
                    } else {
                        echo "<div class='alert alert-danger'>Failed to book. Please try again.</div>";
                    }
                }
            }
        }
    } else {
        echo "<div class='alert alert-warning'>All fields are required.</div>";
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



    <form method="POST" action="">
        <div class="mb-3">
            <label for="service" class="form-label">Select Service:</label>
            <select name="service" class="form-control" required>
                <option value="">-- Choose a Service --</option>
                <?php if ($bookings->num_rows > 0): ?>
                    <?php while ($row = $bookings->fetch_assoc()): ?>
                        <option value="<?= $row['ServiceID']; ?>">
                            <?= htmlspecialchars($row['ServiceName']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
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
