<?php
include '../connection/conn.php';

// Fetch vehicle bookings
$sql = "SELECT b.BookingID, c.Username AS customer_name, c.EmailAddress AS email, 
               s.ServiceName AS service_name, b.Date AS in_time, b.Status
        FROM booking b
        JOIN customer c ON b.CustomerID = c.CustomerID
        JOIN service s ON b.ServiceID = s.ServiceID
        ORDER BY b.Status ASC LIMIT 1000";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body>
<div class="wrapper">
<?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/sidebar.php'; ?>
        <main class="content">
            <div class="container-fluid p-0">
                <h1 class="h3 mb-3">Manage Vehicle Bookings</h1>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="example" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>Email</th>
                                            <th>Service</th>
                                            <th>Booking Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $statusClass = match(strtolower($row["Status"])) {
                                                'pending' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'primary'
                                            };

                                            echo '<tr>
                                                <td>' . $row['BookingID'] . '</td>
                                                <td>' . $row['customer_name'] . '</td>
                                                <td>' . $row['email'] . '</td>
                                                <td>' . $row['service_name'] . '</td>
                                                <td>' . date('F j, Y, g:i A', strtotime($row['in_time'])) . '</td>
                                                <td><span class="badge bg-' . $statusClass . '">' . $row['Status'] . '</span></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm" onclick="editBooking(' . $row['BookingID'] . ')" data-toggle="modal" data-target="#updateModal">
                                                        <i class="fa fa-edit"></i> Update
                                                    </button>
                                                </td>
                                            </tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">No bookings found.</td></tr>';
                                    }
                                    ?>
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

<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Booking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="formData">
                <!-- AJAX Content Here -->
            </div>
        </div>
    </div>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
function editBooking(bookingID) {
    fetch('edit_booking.php?id=' + bookingID)
        .then(response => response.text())
        .then(data => document.getElementById("formData").innerHTML = data);
}
</script>

</body>
</html>
