<?php 
session_start();
include '../../connection/conn.php';

if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$CustomerID = $_SESSION['CustomerID'];

// Handle cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $cancel_id = intval($_POST['cancel_booking_id']);

    $cancel_query = "DELETE FROM bookings WHERE BookingID = ? AND CustomerID = ?";
    $cancel_stmt = $conn->prepare($cancel_query);
    $cancel_stmt->bind_param("ii", $cancel_id, $CustomerID);

    if ($cancel_stmt->execute()) {
        $_SESSION['message'] = 'Booking has been cancelled.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to cancel the booking.';
        $_SESSION['message_type'] = 'danger';
    }
}

// Removed StatusName from select
$query = "SELECT b.BookingID, b.BookingDate, s.ServiceName
          FROM bookings b
          JOIN service s ON b.ServiceID = s.ServiceID
          WHERE b.CustomerID = ?
          ORDER BY b.BookingDate DESC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $CustomerID);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<body>

<?php include './semantic/navbar.php'; ?>

<section id="dashboard" class="container mt-5">
    <h2>Your Booking Dashboard</h2>

    <!-- Flash message -->
    <?php
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        echo "<div class='alert alert-$message_type text-center' id='alert-message'>$message</div>";
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
    ?>

    <div class="row">
    <?php while ($row = $result->fetch_assoc()) : ?>
        <div class="col-md-4 mb-4">
            <div class="card fadeIn">
                <div class="card-body">
                    <h5 class="card-title">Booking #<?php echo $row['BookingID']; ?></h5>
                    <p class="card-text"><strong>Reference:</strong> <?php echo 'REF-' . str_pad($row['BookingID'], 7, '0', STR_PAD_LEFT); ?></p>
                    <p class="card-text"><strong>Service:</strong> <?php echo htmlspecialchars($row['ServiceName']); ?></p>
                    <p class="card-text"><strong>Booking Date:</strong> <?php echo date('Y-m-d H:i', strtotime($row['BookingDate'])); ?></p>

                    <?php
                    $bookingTime = strtotime($row['BookingDate']);
                    $currentTime = time();
                    $cancelDisabled = (($bookingTime - $currentTime) / 3600) <= 2;
                    $bookingID = $row['BookingID'];
                    ?>

                    <?php if ($cancelDisabled): ?>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" disabled title="Cannot cancel less than 2 hours before booking">
                            Cancel
                        </button>
                    <?php else: ?>
                        <button id="cancel-btn-<?php echo $bookingID; ?>" type="button" class="btn btn-sm btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $bookingID; ?>">
                            Cancel
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div class="modal fade" id="cancelModal<?php echo $bookingID; ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?php echo $bookingID; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelModalLabel<?php echo $bookingID; ?>">Cancel Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to cancel booking <strong>#<?php echo $bookingID; ?></strong>?
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="cancel_booking_id" value="<?php echo $bookingID; ?>">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                            <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const alertMessage = document.getElementById('alert-message');
    if (alertMessage) {
        setTimeout(() => {
            alertMessage.style.display = 'none';
        }, 3000);
    }
});
</script>

</body>
</html>

<?php
$conn->close();
?>


<!--  // Define the cancel window (e.g., 2 hours before booking time) -->
<!-- <?php
    
    $bookingTime = strtotime($row['BookingDate']);
    $currentTime = time();
    $diffInHours = ($bookingTime - $currentTime) / 3000;
    $cancelDisabled = $diffInHours <= 2;
    $bookingID = $row['BookingID'];
?>

<?php if ($cancelDisabled): ?>
    <button type="button" class="btn btn-sm btn-secondary mt-2" disabled title="Cannot cancel less than 2 hours before booking">
        Cancel
    </button>
<?php else: ?>
    <button type="button" class="btn btn-sm btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $bookingID; ?>">
        Cancel
    </button>
<?php endif; ?> -->