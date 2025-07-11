<?php
require_once './image.php';
?>

<?php

include '../../connection/conn.php';
require_once '../../admin/includes/reference.php';
include './notification.php';

if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$CustomerID = $_SESSION['CustomerID'];

// Handle cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $cancel_id = intval($_POST['cancel_booking_id']);

    // First, check if the booking belongs to the current customer and is cancellable
    $check_query = "SELECT b.BookingID, b.BookingDate, s.StatusName 
                    FROM bookings b
                    JOIN status s ON b.StatusID = s.StatusID
                    WHERE b.BookingID = ? AND b.CustomerID = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $cancel_id, $CustomerID);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $_SESSION['message'] = 'Booking not found or you do not have permission to cancel it.';
        $_SESSION['message_type'] = 'danger';
        header("Location: dashboard.php");
        exit();
    }

    $booking = $check_result->fetch_assoc();
    $bookingTime = strtotime($booking['BookingDate']);
    $currentTime = time();
    $timeDifference = $bookingTime - $currentTime;

    // Check if booking can be cancelled (more than 2 hours before and status is Pending)
    if ($timeDifference <= 7200 || $booking['StatusName'] !== 'Pending') {
        $_SESSION['message'] = 'This booking cannot be cancelled.';
        $_SESSION['message_type'] = 'danger';
        header("Location: dashboard.php");
        exit();
    }

    // DELETE the booking record instead of updating status
    $delete_query = "DELETE FROM bookings WHERE BookingID = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $cancel_id);

    if ($delete_stmt->execute()) {
        $_SESSION['message'] = 'Booking has been cancelled and removed successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to cancel the booking. Error: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }
    
    // Redirect to prevent re-submission on refresh
    header("Location: dashboard.php");
    exit();
}

// Enhanced query to include employee, car type, and correctly calculate total price and duration
$query = "SELECT
            b.BookingID,
            b.BookingDate,
            b.CarQuantity,
            s.ServiceName,
            s.BasePrice AS ServiceBasePrice,
            ct.TypeName AS CarType,
            ct.BasePrice AS CarTypeBasePrice,
            sct.AdditionalPrice,
            ct.EstimatedDuration AS CarTypeDuration,
            CONCAT(e.FirstName, ' ', e.LastName) AS EmployeeName,
            e.Position AS EmployeePosition,
            st.StatusName,
            st.StatusID,
            -- Calculate total price conditionally
            CASE
                WHEN s.ServiceName LIKE '%Carwash%' AND ct.BasePrice IS NOT NULL THEN
                    (ct.BasePrice + COALESCE(sct.AdditionalPrice, 0)) * b.CarQuantity
                ELSE
                    s.BasePrice * b.CarQuantity
            END AS TotalPrice,
            -- Estimated Duration for Carwash services
            CASE
                WHEN s.ServiceName LIKE '%Carwash%' AND ct.EstimatedDuration IS NOT NULL THEN
                    ct.EstimatedDuration
                ELSE
                    NULL
            END AS EstimatedDuration
          FROM bookings b
          LEFT JOIN service s ON b.ServiceID = s.ServiceID
          LEFT JOIN car_types ct ON b.CarTypeID = ct.CarTypeID
          LEFT JOIN service_car_types sct ON s.ServiceID = sct.ServiceID AND ct.CarTypeID = sct.CarTypeID
          LEFT JOIN employees e ON b.EmployeeID = e.EmployeeID
          LEFT JOIN status st ON b.StatusID = st.StatusID
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

    <?php
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        echo "<div class='alert alert-$message_type text-center alert-dismissible fade show' role='alert' id='alert-message'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
    ?>

    <div class="row">
    <?php while ($row = $result->fetch_assoc()) :
        // Determine if it's a carwash service for display logic
        $is_carwash_service = (strpos($row['ServiceName'], 'Carwash') !== false);
        
        $bookingTime = strtotime($row['BookingDate']);
        $currentTime = time();
        $timeDifference = $bookingTime - $currentTime;
        
        // Disable cancel button if:
        // 1. Less than 2 hours before booking
        // 2. Status is not Pending (1)
        $cancelDisabled = ($timeDifference <= 7200) || ($row['StatusID'] != 1);
        $bookingID = $row['BookingID'];
    ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card fadeIn h-100">
                <div class="card-body">
                    <h5 class="card-title">Booking #<?php echo $row['BookingID']; ?></h5>
                    <p class="card-text"><strong>Reference:</strong> <?php echo 'REF-' . str_pad($row['BookingID'], 7, '0', STR_PAD_LEFT); ?></p>
                    
                    <hr class="my-3">
                    
                    <p class="card-text"><strong>Service:</strong> <?php echo htmlspecialchars($row['ServiceName']); ?></p>
                    <p class="card-text"><strong>Booking Date:</strong> <?php echo date('Y-m-d H:i', strtotime($row['BookingDate'])); ?></p>
                    <p class="card-text"><strong>Status:</strong> 
                        <span class="badge bg-<?php echo $row['StatusName'] == 'Pending' ? 'warning' : ($row['StatusName'] == 'Completed' ? 'success' : ($row['StatusName'] == 'In Progress' ? 'info' : ($row['StatusName'] == 'Cancelled' ? 'danger' : 'secondary'))); ?>">
                            <?php echo htmlspecialchars($row['StatusName'] ?? 'Pending'); ?>
                        </span>
                    </p>
                    
                    <hr class="my-3">
                    
                    <?php if ($is_carwash_service): ?>
                        <p class="card-text"><strong>Car Type:</strong> <?php echo htmlspecialchars($row['CarType'] ?? 'N/A'); ?></p>
                        <p class="card-text"><strong>Estimated Duration:</strong> <?php echo htmlspecialchars($row['EstimatedDuration'] ? $row['EstimatedDuration'] . ' minutes' : 'N/A'); ?></p>
                    <?php else: ?>
                        <p class="card-text"><strong>Car Type:</strong> Not applicable</p>
                    <?php endif; ?>
                    <p class="card-text"><strong>Number of Cars:</strong> <?php echo $row['CarQuantity']; ?></p>
                    
                    <hr class="my-3">
                    
                    <p class="card-text"><strong>Assigned Employee:</strong> 
                        <?php if (!empty($row['EmployeeName'])): ?>
                            <?php echo htmlspecialchars($row['EmployeeName']); ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($row['EmployeePosition']); ?></small>
                        <?php else: ?>
                            <span class="text-muted">No preferred employee / Not yet assigned</span>
                        <?php endif; ?>
                    </p>
                    
                    <hr class="my-3">
                    
                    <p class="card-text"><strong>Total Price:</strong> 
                        <span class="text-success fw-bold">₱<?php echo number_format($row['TotalPrice'], 2); ?></span>
                    </p>
                    
                    <p class="mb-2">
                        <button class="btn btn-link btn-sm p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#priceBreakdown<?php echo $row['BookingID']; ?>" aria-expanded="false" aria-controls="priceBreakdown<?php echo $row['BookingID']; ?>">
                            View price breakdown
                        </button>
                    </p>
                    <div class="collapse" id="priceBreakdown<?php echo $row['BookingID']; ?>">
                        <div class="card card-body bg-light">
                            <small>
                                <?php if ($is_carwash_service): ?>
                                    Car Type Base Price: ₱<?php echo number_format($row['CarTypeBasePrice'] ?? 0, 2); ?><br>
                                    <?php if ($row['AdditionalPrice'] > 0): ?>
                                        Additional Service Price: ₱<?php echo number_format($row['AdditionalPrice'], 2); ?><br>
                                    <?php endif; ?>
                                    Subtotal per car: ₱<?php echo number_format(($row['CarTypeBasePrice'] ?? 0) + ($row['AdditionalPrice'] ?? 0), 2); ?><br>
                                <?php else: ?>
                                    Service Base Price: ₱<?php echo number_format($row['ServiceBasePrice'], 2); ?><br>
                                <?php endif; ?>
                                Quantity: <?php echo $row['CarQuantity']; ?> car(s)<br>
                                <hr class="my-1">
                                <strong>Total: ₱<?php echo number_format($row['TotalPrice'], 2); ?></strong>
                            </small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <?php if ($cancelDisabled): ?>
                            <button type="button" class="btn btn-sm btn-secondary" disabled
                                title="<?php
                                if ($row['StatusID'] == 4) echo 'Cannot cancel a completed booking.';
                                else if ($row['StatusID'] == 3) echo 'This booking is already cancelled.';
                                else if ($row['StatusID'] == 2) echo 'Cannot cancel a booking that is in progress.';
                                else echo 'Cannot cancel less than 2 hours before the booking.';
                                ?>">
                                Cancel
                            </button>
                        <?php else: ?>
                            <button id="cancel-btn-<?php echo $bookingID; ?>" type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $bookingID; ?>">
                                Cancel
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Confirmation Modal -->
        <div class="modal fade" id="cancelModal<?php echo $bookingID; ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?php echo $bookingID; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="cancelModalLabel<?php echo $bookingID; ?>">Confirm Cancellation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to cancel and delete this booking?</p>
                            <div class="alert alert-warning">
                                <strong>Booking Details:</strong><br>
                                Service: <?php echo htmlspecialchars($row['ServiceName']); ?><br>
                                Date: <?php echo date('Y-m-d H:i', strtotime($row['BookingDate'])); ?><br>
                                <?php if ($is_carwash_service): ?>
                                    Car Type: <?php echo htmlspecialchars($row['CarType'] ?? 'N/A'); ?><br>
                                <?php endif; ?>
                                Quantity: <?php echo $row['CarQuantity']; ?><br>
                                Total: ₱<?php echo number_format($row['TotalPrice'], 2); ?>
                            </div>
                            <div class="alert alert-danger">
                                <small><i class="bi bi-exclamation-triangle"></i> Warning: This action will permanently delete your booking!</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="cancel_booking_id" value="<?php echo $bookingID; ?>">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Booking</button>
                            <button type="submit" class="btn btn-danger">Yes, Delete Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    
    <?php if ($result->num_rows == 0): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <h4>No Bookings Found</h4>
                <p>You haven't made any bookings yet. <a href="services.php" class="alert-link">Browse our services</a> to make your first booking!</p>
            </div>
        </div>
    <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Auto-close alerts after 3 seconds
    const alertMessage = document.getElementById('alert-message');
    if (alertMessage) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alertMessage);
            bsAlert.close();
        }, 3000);
    }
});
</script>

</body>
</html>

<?php
$conn->close();
?>