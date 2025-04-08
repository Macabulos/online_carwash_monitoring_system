<?php
session_start();
require_once '../connection/conn.php'; // Include database connection

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Fetch all bookings with related data (status and customer info)
$sql = "SELECT b.BookingID, b.BookingDate, b.StatusID, c.Username AS CustomerName, s.ServiceName
        FROM bookings b
        JOIN customer c ON b.CustomerID = c.CustomerID
        JOIN service s ON b.ServiceID = s.ServiceID
        ORDER BY b.BookingID ASC";
$result = mysqli_query($conn, $sql);

// Handle update request
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status_id = $_POST['status_id'];
    $update_query = "UPDATE bookings SET StatusID = ? WHERE BookingID = ?";

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $status_id, $booking_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Booking status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating booking status.";
    }
    header("Location: manage_bookings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body>
<div class="wrapper">
    <?php include 'includes/nav.php'; ?>
    <div class="main">
        <?php include 'includes/navtop.php'; ?>
        <main class="content">
            <div class="container-fluid p-0">
                <h1 class="h3 mb-3">Manage Bookings</h1>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php elseif (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="example" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer Name</th>
                                            <th>Service</th>
                                            <th>Booking Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $row['BookingID']; ?></td>
                                            <td><?php echo $row['CustomerName']; ?></td>
                                            <td><?php echo $row['ServiceName']; ?></td>
                                            <td><?php echo $row['BookingDate']; ?></td>
                                            <td><?php echo $row['StatusID'] == 1 ? 'Pending' : ($row['StatusID'] == 2 ? 'Completed' : 'Cancelled'); ?></td>
                                            <td>
                                                <button class="btn btn-info btn-sm" onclick="editBooking(<?php echo $row['BookingID']; ?>)" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                                    <i class="fa fa-edit"></i> Update Status
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include 'includes/footer.php'; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="manage_bookings.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="updateStatusModalLabel">Update Booking Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="booking_id" id="booking_id">
          <div class="mb-3">
              <label for="status_id" class="form-label">Status</label>
              <select class="form-control" name="status_id" id="status_id" required>
                  <option value="1">Pending</option>
                  <option value="2">Completed</option>
                  <option value="3">Cancelled</option>
              </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
function editBooking(bookingID) {
    // Set the booking ID for the modal
    document.getElementById("booking_id").value = bookingID;
}
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
