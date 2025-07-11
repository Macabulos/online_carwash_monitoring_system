<?php
    session_start();
    require_once '../connection/conn.php';
    require_once 'includes/reference.php'; // Booking reference function

    // Admin session check
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        $_SESSION['error_message'] = "Unauthorized access. Please log in.";
        header("Location: ../auth/login.php");
        exit;
    }

    // Handle delete booking
    if (isset($_POST['delete_booking'])) {
        $delete_id = $_POST['delete_booking_id'];
        $stmt = $conn->prepare("DELETE FROM bookings WHERE BookingID = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Booking deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting booking.";
        }
        header("Location: manage_bookings.php");
        exit();
    }

    // Handle status update
    if (isset($_POST['update_status'])) {
        $booking_id = $_POST['booking_id'];
        $status_id = $_POST['status_id'];
        $stmt = $conn->prepare("UPDATE bookings SET StatusID = ? WHERE BookingID = ?");
        $stmt->bind_param("ii", $status_id, $booking_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Booking status updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating booking status.";
        }
        header("Location: manage_bookings.php");
        exit();
    }

    // Pagination
    $limit = 10;
    $page = max((int)($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    $totalBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings"))['total'];
    $totalPages = ceil($totalBookings / $limit);

    // Main query with price calculation and employee information
    $query = "
        SELECT 
            b.BookingID, 
            b.BookingDate, 
            b.StatusID, 
            c.Username AS CustomerName, 
            s.ServiceName, 
            st.StatusName,
            ct.TypeName AS CarType, 
            b.CarQuantity,
            ct.BasePrice AS CarTypePrice,
            s.BasePrice AS ServicePrice,
            (b.CarQuantity * (IFNULL(ct.BasePrice, 0) + IFNULL(s.BasePrice, 0))) AS TotalPrice,
            CONCAT(e.FirstName, ' ', e.LastName) AS EmployeeName,
            e.EmployeeID
        FROM bookings b
        JOIN customer c ON b.CustomerID = c.CustomerID
        JOIN service s ON b.ServiceID = s.ServiceID
        JOIN status st ON b.StatusID = st.StatusID
        LEFT JOIN car_types ct ON b.CarTypeID = ct.CarTypeID
        LEFT JOIN employees e ON b.EmployeeID = e.EmployeeID
        ORDER BY b.BookingDate DESC
        LIMIT $limit OFFSET $offset
    ";

    $result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<style>
    /* Responsive table styles */
    .table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-responsive {
        min-height: .01%;
        overflow-x: auto;
    }
    
    @media screen and (max-width: 1200px) {
        .table-responsive {
            width: 100%;
            margin-bottom: 15px;
            overflow-y: hidden;
            -ms-overflow-style: -ms-autohiding-scrollbar;
            border: 1px solid #ddd;
        }
        
        .table-responsive > .table {
            margin-bottom: 0;
        }
        
        .table-responsive > .table > thead > tr > th,
        .table-responsive > .table > tbody > tr > th,
        .table-responsive > .table > tfoot > tr > th,
        .table-responsive > .table > thead > tr > td,
        .table-responsive > .table > tbody > tr > td,
        .table-responsive > .table > tfoot > tr > td {
            white-space: nowrap;
        }
    }
    
    /* Improved table styling */
    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    
    .table th {
        background-color: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .table th, .table td {
        padding: 0.75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }
    
    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table tbody + tbody {
        border-top: 2px solid #dee2e6;
    }
    
    /* Action buttons */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
        margin: 2px;
    }
    
    /* Pagination styling */
    .pagination {
        flex-wrap: wrap;
    }
    
    .page-item {
        margin: 2px;
    }
    
    /* Card styling */
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    /* Modal adjustments */
    .modal-content {
        border-radius: 0.3rem;
    }
    
    /* Responsive text */
    @media (max-width: 768px) {
        h1.h3 {
            font-size: 1.5rem;
        }
        
        .table th, .table td {
            padding: 0.5rem;
            font-size: 0.8rem;
        }
        
        .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }
    }
</style>
<body>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/navbar.php'; ?>
        <main class="content">
            <div class="container-fluid p-0">
                <h1 class="h3 mb-3">Manage Bookings</h1>

                <!-- Alerts -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Car Type</th>
                                            <th>Qty</th>
                                            <th>Service ₱</th>
                                            <th>Car Type ₱</th>
                                            <th>Total ₱</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Employee</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                            <tr>
                                                <td><strong><?= generateBookingReference($row['BookingID']); ?></strong></td>
                                                <td><?= $row['BookingID']; ?></td>
                                                <td><?= htmlspecialchars($row['CustomerName']); ?></td>
                                                <td><?= htmlspecialchars($row['ServiceName']); ?></td>
                                                <td><?= htmlspecialchars($row['CarType'] ?? 'N/A'); ?></td>
                                                <td><?= $row['CarQuantity']; ?></td>
                                                <td>₱<?= number_format($row['ServicePrice'] ?? 0, 2); ?></td>
                                                <td>₱<?= number_format($row['CarTypePrice'] ?? 0, 2); ?></td>
                                                <td>₱<?= number_format($row['TotalPrice'] ?? 0, 2); ?></td>
                                                <td><?= date('m/d H:i', strtotime($row['BookingDate'])); ?></td>
                                                <td><?= htmlspecialchars($row['StatusName']); ?></td>
                                                <td><?= $row['EmployeeName'] ? htmlspecialchars($row['EmployeeName']) : 'N/A'; ?></td>
                                                <td>
                                                    <div class="d-flex flex-wrap">
                                                        <button class="btn btn-info btn-sm" onclick="editBooking(<?= $row['BookingID']; ?>)" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="setDeleteBookingID(<?= $row['BookingID']; ?>)" data-bs-toggle="modal" data-bs-target="#deleteBookingModal">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-end mt-3">
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a></li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1; ?>">Next</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
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
    <form action="manage_bookings.php" method="POST" class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Update Booking Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="booking_id" id="booking_id">
        <label>Status</label>
        <select class="form-control" name="status_id" required style="padding: 2px;">
        <option value="1">Pending</option>
        <option value="2">Completed</option>
        <option value="3">Cancelled</option>
        <option value="4">In Progress</option>
        </select>
    </div>
    <div class="modal-footer">
        <button type="submit" name="update_status" class="btn btn-success">Update</button>
    </div>
    </form>
</div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1" aria-labelledby="deleteBookingModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <form method="POST" action="manage_bookings.php" class="modal-content">
    <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        Are you sure you want to delete this booking?
        <input type="hidden" name="delete_booking_id" id="delete_booking_id">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="delete_booking" class="btn btn-danger">Delete</button>
    </div>
    </form>
</div>
</div>

<!-- Scripts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/scripts.php'; ?>

<script>
function editBooking(bookingID) {
    document.getElementById("booking_id").value = bookingID;
}
function setDeleteBookingID(id) {
    document.getElementById("delete_booking_id").value = id;
}
setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
    }
}, 5000);
</script>

</body>
</html>