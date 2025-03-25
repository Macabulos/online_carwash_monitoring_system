<?php
session_start();
require_once '../connection/conn.php'; // Database connection

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Fetch all customers with bookings (since booking table was removed)
$booking_query = "SELECT c.CustomerID, c.Username AS customer_name, c.EmailAddress AS email, 
                         s.ServiceName AS service, c.BookingDate AS booking_date, c.Status 
                  FROM customer c
                  LEFT JOIN service s ON c.ServiceID = s.ServiceID
                  WHERE c.ServiceID IS NOT NULL
                  ORDER BY c.BookingDate DESC";

$bookings = mysqli_query($conn, $booking_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Manage Bookings</title>
</head>
<body>
<div class="wrapper">
    <?php include 'includes/nav.php'; ?> <!-- Sidebar -->
    <div class="main">
        <?php include 'includes/navtop.php'; ?> <!-- Top Navbar -->
        <main class="content">
            <div class="container-fluid">
                <h1 class="h3 mb-3">Manage Bookings</h1>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Bookings</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="bookingsTable">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Service</th>
                                    <th>Booking Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
    <?php while ($row = mysqli_fetch_assoc($bookings)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['CustomerID']); ?></td>
            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['service'] ?? 'N/A'); ?></td>
            <td><?php echo date('F j, Y, g:i A', strtotime($row['booking_date'])); ?></td>
            <td>
                <span class="badge bg-<?php echo strtolower($row['Status']) == 'pending' ? 'warning' : (strtolower($row['Status']) == 'completed' ? 'success' : 'danger'); ?>">
                    <?php echo htmlspecialchars($row['Status']); ?>
                </span>
            </td>
            <td>
    <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $row['CustomerID']; ?>" data-toggle="modal" data-target="#editBookingModal">
        <i class="fa fa-edit"></i> Edit
    </button>
    <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['CustomerID']; ?>" data-toggle="modal" data-target="#deleteBookingModal">
        <i class="fa fa-trash"></i> Delete
    </button>
</td>


        </tr>
    <?php endwhile; ?>
</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBookingModalLabel">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editBookingForm">
                <!-- AJAX Content Will Load Here -->
            </div>
        </div>
    </div>
</div>


<!-- Delete Booking Modal -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Booking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this booking?</p>
                <form id="deleteBookingForm" method="POST">
                    <input type="hidden" name="delete_booking_id" id="delete_booking_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- <?php include 'includes/scripts.php'; ?> -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable();

    // Open Edit Booking Modal with AJAX
    $(".edit-btn").click(function() {
        var customerID = $(this).data("id");
        $.ajax({
            url: "edit_booking.php",
            type: "GET",
            data: { id: customerID },
            success: function(response) {
                $("#editBookingForm").html(response);
                var modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
                modal.show();
            }
        });
    });

    // Open Delete Modal
    $(".delete-btn").click(function() {
        var customerID = $(this).data("id");
        $("#delete_booking_id").val(customerID);
        var modal = new bootstrap.Modal(document.getElementById('deleteBookingModal'));
        modal.show();
    });

    // Handle Delete Submission
    $("#deleteBookingForm").submit(function(e) {
        e.preventDefault();
        var bookingID = $("#delete_booking_id").val();
        $.ajax({
            url: "delete_booking.php",
            type: "POST",
            data: { id: bookingID },
            success: function(response) {
                alert("Booking deleted successfully!");
                location.reload();
            }
        });
    });
});
</script>
