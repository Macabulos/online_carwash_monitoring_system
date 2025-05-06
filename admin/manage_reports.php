<?php
session_start();
require_once '../connection/conn.php'; // DB connection

// Check admin login
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

$query = "
SELECT 
    b.BookingID,
    c.Username,
    c.EmailAddress,
    s.ServiceName,
    b.BookingDate,
    st.StatusName
FROM bookings b
JOIN customer c ON b.CustomerID = c.CustomerID
JOIN service s ON b.ServiceID = s.ServiceID
JOIN status st ON b.StatusID = st.StatusID
ORDER BY b.BookingDate DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error generating report: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Activity Report</title>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main">
            <?php include 'includes/navbar.php'; ?>
            <main class="content">
                <div class="container-fluid">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
         
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        
                    </div>
                <?php endif; ?>

                    <h1 class="h3 mb-3">All Booking Activities Report</h1>
                    <div class="card">
                        <div class="card-body">
                        <div class="mb-3 d-flex justify-content-between">
                            <form action="export_activity_report_excel.php" method="POST">
                                <button type="submit" class="btn btn-success">
                                    <i class="feather icon-download"></i> Export to Excel
                                </button>
                            </form>
                            <!-- Trigger Delete Modal -->
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteReportModal">
                            <i class="feather icon-trash-2"></i> Delete All Reports
                        </button>

                        </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer Name</th>
                                            <th>Email</th>
                                            <th>Service</th>
                                            <th>Booking Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $row['BookingID']; ?></td>
                                                <td><?php echo htmlspecialchars($row['Username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['EmailAddress']); ?></td>
                                                <td><?php echo htmlspecialchars($row['ServiceName']); ?></td>
                                                <td class="text-center"><?php echo date('Y-m-d h:i A', strtotime($row['BookingDate'])); ?></td>
                                                <td class="text-center"><?php echo $row['StatusName']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if (mysqli_num_rows($result) === 0): ?>
                                            <tr><td colspan="6" class="text-center">No records found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="deleteReportModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deleteReportModalLabel">Confirm Deletion</h5>
                            <!-- <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> -->
                        </div>
                        <div class="modal-body text-center">
                            <p class="mb-0">Are you sure you want to delete <strong>all report records</strong>? This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form action="delete_activity_report.php" method="POST">
                                <button type="submit" class="btn btn-danger">Yes, Delete All</button>
                            </form>
                        </div>
                        </div>
                    </div>
                    </div>
            </main>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    <?php include 'includes/scripts.php'; ?>
    <script>

setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }
  }, 3000);

function editService(serviceID) {
    fetch('edit_service.php?id=' + serviceID)
        .then(response => response.text())
        .then(data => document.getElementById("editServiceData").innerHTML = data);
}
</script>
</body>
</html>
