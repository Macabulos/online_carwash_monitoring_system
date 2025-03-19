<?php
include 'includes/head.php';
include '../connection/conn.php';

// Fetch all bookings
$booking_query = "SELECT b.BookingID, c.Username AS customer_name, s.ServiceName AS service, b.Date AS booking_date, b.Status 
                  FROM booking b
                  LEFT JOIN customer c ON b.CustomerID = c.CustomerID
                  LEFT JOIN service s ON b.ServiceID = s.ServiceID
                  ORDER BY b.Date DESC";
$bookings = mysqli_query($conn, $booking_query);
?>

<!DOCTYPE html>
<html lang="en">
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
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Booking Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($bookings)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['BookingID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['service']); ?></td>
                                        <td><?php echo date('F j, Y, g:i A', strtotime($row['booking_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo strtolower($row['Status']) == 'pending' ? 'warning' : (strtolower($row['Status']) == 'completed' ? 'success' : 'danger'); ?>">
                                                <?php echo htmlspecialchars($row['Status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_booking.php?id=<?php echo $row['BookingID']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                            <a href="delete_booking.php?id=<?php echo $row['BookingID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
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
<?php include 'includes/scripts.php'; ?>
<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable();
});
</script>
</body>
</html>
