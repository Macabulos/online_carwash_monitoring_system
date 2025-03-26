<?php
session_start();
require_once '../connection/conn.php'; // Database connection

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// ✅ Fetch all customers, even those without services booked
$booking_query = "SELECT c.CustomerID, c.Username AS customer_name, c.EmailAddress AS email, 
                         COALESCE(s.ServiceName, 'No Service') AS service, c.BookingDate AS booking_date, 
                         COALESCE(st.StatusName, 'Pending') AS status
                  FROM customer c
                  LEFT JOIN service s ON c.ServiceID = s.ServiceID
                  LEFT JOIN status st ON c.StatusID = st.StatusID
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
                                        <td><?php echo htmlspecialchars($row['service']); ?></td>
                                        <td>
                                            <?php echo !empty($row['booking_date']) ? date('F j, Y, g:i A', strtotime($row['booking_date'])) : 'No Booking Date'; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $status_lower = strtolower($row['status']);
                                                $badge_class = 'secondary'; // Default

                                                if ($status_lower == 'pending') {
                                                    $badge_class = 'warning';
                                                } elseif ($status_lower == 'completed') {
                                                    $badge_class = 'success';
                                                } elseif ($status_lower == 'cancelled') {
                                                    $badge_class = 'danger';
                                                }
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm edit-btn" onclick="editBooking(<?php echo $row['CustomerID']; ?>)" data-bs-toggle="modal" data-bs-target="#editBookingModal">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['CustomerID']; ?>" data-bs-toggle="modal" data-bs-target="#deleteBookingModal">
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

<!-- ✅ EDIT BOOKING MODAL -->
<div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBookingModalLabel">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editBookingForm">
                <!-- The form will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- ✅ DELETE BOOKING MODAL -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1" aria-labelledby="deleteBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBookingModalLabel">Delete Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this booking?</p>
                <form id="deleteBookingForm">
                    <input type="hidden" name="delete_booking_id" id="delete_booking_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ✅ jQuery & Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ✅ Fetch and load edit form
    function editBooking(customerID) {
        fetch('edit_booking.php?id=' + customerID)
            .then(response => response.text())
            .then(data => {
                document.getElementById("editBookingForm").innerHTML = data;
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                document.getElementById("editBookingForm").innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load booking details. 
                        <button class="btn btn-sm btn-warning" onclick="location.reload()">Retry</button>
                    </div>`;
            });
    }

    // ✅ Open Delete Modal
    document.addEventListener("click", function (event) {
        let target = event.target.closest(".delete-btn");
        if (target) {
            let customerID = target.getAttribute("data-id");
            document.getElementById("delete_booking_id").value = customerID;
        }
    });

    // ✅ Handle Delete Submission
    document.getElementById("deleteBookingForm").addEventListener("submit", function (e) {
        e.preventDefault();
        let customerID = document.getElementById("delete_booking_id").value;

        fetch("delete_booking.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + encodeURIComponent(customerID)
        })
        .then(response => response.text())
        .then(() => {
            alert("Booking deleted successfully!");
            location.reload();
        })
        .catch(error => {
            console.error("Fetch Error:", error);
            alert("Error deleting booking.");
        });
    });
</script>

</body>
</html>
