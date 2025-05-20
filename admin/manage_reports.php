<?php
session_start();
require_once '../connection/conn.php'; // DB connection

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Get current month and year as default
$current_month = date('m');
$current_year = date('Y');

// Get selected month/year from request or use current
$selected_month = $_GET['month'] ?? $current_month;
$selected_year = $_GET['year'] ?? $current_year;

// Validate month/year
if (!is_numeric($selected_month)) $selected_month = $current_month;
if (!is_numeric($selected_year)) $selected_year = $current_year;

// Build query with month filter
$query = "
SELECT 
    b.BookingID,
    c.Username,
    c.EmailAddress,
    s.ServiceName,
    b.BookingDate,
    st.StatusName,
    s.BasePrice,
    ct.TypeName,
    ct.BasePrice AS CarTypePrice,
    b.CarQuantity,
    (CASE 
        WHEN s.ServiceName LIKE '%Carwash%' THEN ct.BasePrice * b.CarQuantity
        ELSE s.BasePrice * b.CarQuantity
    END) AS TotalPrice
FROM bookings b
JOIN customer c ON b.CustomerID = c.CustomerID
JOIN service s ON b.ServiceID = s.ServiceID
JOIN status st ON b.StatusID = st.StatusID
LEFT JOIN car_types ct ON b.CarTypeID = ct.CarTypeID
WHERE MONTH(b.BookingDate) = ? AND YEAR(b.BookingDate) = ?
ORDER BY b.BookingDate DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $selected_month, $selected_year);
$stmt->execute();
$result = $stmt->get_result();

// Calculate totals
$total_bookings = 0;
$total_revenue = 0;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $total_bookings++;
        $total_revenue += $row['TotalPrice'];
    }
    $result->data_seek(0); // Reset pointer for display
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Monthly Activity Report</title>
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

                    <h1 class="h3 mb-3">Monthly Booking Activities Report</h1>
                    
                    <!-- Month/Year Filter Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="month" class="form-label">Month</label>
                                    <select class="form-select" id="month" name="month">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?= $m ?>" <?= $m == $selected_month ? 'selected' : '' ?>>
                                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year">
                                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                            <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>>
                                                <?= $y ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Bookings</h5>
                                    <h2 class="card-text"><?= $total_bookings ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Revenue</h5>
                                    <h2 class="card-text">₱<?= number_format($total_revenue, 2) ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 d-flex justify-content-between">
                                <form action="export_activity_report_excel.php" method="POST">
                                    <input type="hidden" name="month" value="<?= $selected_month ?>">
                                    <input type="hidden" name="year" value="<?= $selected_year ?>">
                                    <button type="submit" class="btn btn-success">
                                        <i class="feather icon-download"></i> Export to Excel
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteReportModal">
                                    <i class="feather icon-trash-2"></i> Delete Reports
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Date & Time</th>
                                            <th>Car Type</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result && $result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="text-center"><?= $row['BookingID'] ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($row['Username']) ?><br>
                                                        <small><?= htmlspecialchars($row['EmailAddress']) ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['ServiceName']) ?></td>
                                                    <td class="text-center"><?= date('M j, Y h:i A', strtotime($row['BookingDate'])) ?></td>
                                                    <td><?= $row['TypeName'] ?? 'N/A' ?></td>
                                                    <td class="text-center"><?= $row['CarQuantity'] ?></td>
                                                    <td class="text-center"><?= $row['StatusName'] ?></td>
                                                    <td class="text-end">₱<?= number_format($row['TotalPrice'], 2) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="8" class="text-center">No records found for selected month.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th colspan="7" class="text-end">Total Revenue:</th>
                                            <th class="text-end">₱<?= number_format($total_revenue, 2) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="deleteReportModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="deleteReportModalLabel">Confirm Deletion</h5>
                                </div>
                                <div class="modal-body text-center">
                                    <p class="mb-0">Are you sure you want to delete all reports for <?= date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)) ?>?</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form action="delete_activity_report.php" method="POST">
                                        <input type="hidden" name="month" value="<?= $selected_month ?>">
                                        <input type="hidden" name="year" value="<?= $selected_year ?>">
                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    </form>
                                </div>
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
    </script>
</body>
</html>