<?php 
session_start();
require_once '../connection/conn.php'; // Database connection

        // Ensure the user is logged in as an admin
        if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
            $_SESSION['error_message'] = "Unauthorized access. Please log in.";
            header("Location: ../auth/login.php");
            exit;
        }

        require_once 'includes/reference.php'; // Include reference generator

        // Fetch recent 10 bookings
        // Pagination setup
        $limit = 10; // Number of bookings per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max($page, 1); // Ensure page is at least 1
        $offset = ($page - 1) * $limit;

        // Count total bookings
        $countQuery = "SELECT COUNT(*) AS total FROM bookings";
        $countResult = mysqli_query($conn, $countQuery);
        $totalBookings = mysqli_fetch_assoc($countResult)['total'];
        $totalPages = ceil($totalBookings / $limit);

        // Fetch bookings with LIMIT and OFFSET
        $bookingQuery = "
            SELECT b.BookingID, b.BookingDate, c.Username, s.ServiceName, st.StatusName
            FROM bookings b
            JOIN customer c ON b.CustomerID = c.CustomerID
            JOIN service s ON b.ServiceID = s.ServiceID
            JOIN status st ON b.StatusID = st.StatusID
            ORDER BY b.BookingDate DESC
            LIMIT $limit OFFSET $offset
        ";
        $bookingResult = mysqli_query($conn, $bookingQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Recent Bookings</title>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main">
            <?php include 'includes/navbar.php'; ?>
            <main class="content">
                <!-- Recent Bookings with References -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Bookings with References</h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($bookingResult)) : ?>
                                            <tr>
                                                <td><strong><?php echo generateBookingReference($row['BookingID']); ?></strong></td>
                                                <td><?php echo $row['BookingID']; ?></td>
                                                <td><?php echo htmlspecialchars($row['Username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['ServiceName']); ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($row['BookingDate'])); ?></td>
                                                <td><?php echo htmlspecialchars($row['StatusName']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-end mt-3">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
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
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
